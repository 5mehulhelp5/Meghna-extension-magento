<?php
declare(strict_types=1);
namespace Codilar\LoyaltyWallet\Observer;

use Codilar\LoyaltyWallet\Api\Data\LoyaltyLedgerInterfaceFactory;
use Codilar\LoyaltyWallet\Api\Data\StoreWalletInterfaceFactory;
use Codilar\LoyaltyWallet\Api\LoyaltyLedgerRepositoryInterface;
use Codilar\LoyaltyWallet\Api\StoreWalletRepositoryInterface;
use Codilar\LoyaltyWallet\Logger\Logger as WalletLogger;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReverseLoyaltyPointsOnCancel implements ObserverInterface
{
    public function __construct(
        private readonly LoyaltyLedgerInterfaceFactory $ledgerFactory,
        private readonly LoyaltyLedgerRepositoryInterface $ledgerRepository,
        private readonly StoreWalletInterfaceFactory $storeWalletFactory,
        private readonly StoreWalletRepositoryInterface $storeWalletRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly WalletLogger $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order || !$order->getIncrementId()) {
            return;
        }

        $incrementId = $order->getIncrementId();
        $customerId = (int)$order->getCustomerId();
        $grandTotal = (float)$order->getGrandTotal();

        if (!$customerId) {
            return;
        }

        try {
            // ==========================================
            // 1. REVERSE LOYALTY POINTS
            // ==========================================
            $criteriaBuilder1 = $this->searchCriteriaBuilderFactory->create();
            $criteriaBuilder1->addFilter('order_increment_id', $incrementId);
            $existingEntries = $this->ledgerRepository->getList($criteriaBuilder1->create())->getItems();

            $originalEarned = 0;
            $alreadyReversed = false;

            foreach ($existingEntries as $entry) {
                $qty = (int)$entry->getQuantity();
                if ($qty > 0) {
                    $originalEarned += $qty;
                } elseif ($qty < 0 && $entry->getComment() === 'Order Canceled') {
                    $alreadyReversed = true;
                }
            }

            if ($originalEarned > 0 && !$alreadyReversed) {
                $reversalQuantity = -$originalEarned;

                $ledger = $this->ledgerFactory->create();
                $ledger->setCustomerId($customerId);
                $ledger->setOrderIncrementId($incrementId);
                $ledger->setQuantity($reversalQuantity);
                $ledger->setComment('Order Canceled');

                // Calculate running balance for loyalty points with explicit chronological sorting
                $criteriaBuilderBalance = $this->searchCriteriaBuilderFactory->create();
                $criteriaBuilderBalance->addFilter('customer_id', $customerId);

                // Explicitly force chronological order (oldest to newest) to prevent math offset errors
                $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setAscendingDirection()->create();
                $criteriaBuilderBalance->setSortOrders([$sortOrder]);

                $allCustomerEntries = $this->ledgerRepository->getList($criteriaBuilderBalance->create())->getItems();

                $currentBalance = 0;
                foreach ($allCustomerEntries as $item) {
                    $currentBalance += (int)$item->getQuantity();
                }
                $newBalanceAfter = $currentBalance + $reversalQuantity;
                $ledger->setBalanceAfter(max(0, $newBalanceAfter));

                $ledger->setCreatedAt(date('Y-m-d H:i:s'));

                $this->ledgerRepository->save($ledger);
                $this->logger->info("Loyalty Ledger: Reversed {$originalEarned} points for canceled order #{$incrementId}.");
            }

            // ==========================================
            // 2. REFUND ORDER AMOUNT TO STORE CASH WALLET
            // ==========================================
            $criteriaBuilderWalletCheck = $this->searchCriteriaBuilderFactory->create();
            $criteriaBuilderWalletCheck->addFilter('order_increment_id', $incrementId);
            $criteriaBuilderWalletCheck->addFilter('comment', 'Order Refund / Cancellation');
            $existingWalletEntries = $this->storeWalletRepository->getList($criteriaBuilderWalletCheck->create())->getItems();

            if (empty($existingWalletEntries) && $grandTotal > 0) {
                $storeWallet = $this->storeWalletFactory->create();
                $storeWallet->setCustomerId($customerId);
                $storeWallet->setOrderIncrementId($incrementId);
                $storeWallet->setAmount($grandTotal); // Positive credit
                $storeWallet->setComment('Order Refund / Cancellation');

                // Calculate running balance for store cash wallet with chronological sorting
                $criteriaBuilderWalletBalance = $this->searchCriteriaBuilderFactory->create();
                $criteriaBuilderWalletBalance->addFilter('customer_id', $customerId);

                $walletSortOrder = $this->sortOrderBuilder->setField('entity_id')->setAscendingDirection()->create();
                $criteriaBuilderWalletBalance->setSortOrders([$walletSortOrder]);

                $allWalletEntries = $this->storeWalletRepository->getList($criteriaBuilderWalletBalance->create())->getItems();

                $currentCashBalance = 0.0;
                foreach ($allWalletEntries as $wItem) {
                    $currentCashBalance += (float)$wItem->getAmount();
                }
                $newCashBalanceAfter = $currentCashBalance + $grandTotal;

                $storeWallet->setBalanceAfter(max(0, $newCashBalanceAfter));

                $this->storeWalletRepository->save($storeWallet);
                $this->logger->info("Store Wallet: Credited {$grandTotal} for canceled order #{$incrementId}.");
            }

        } catch (\Exception $e) {
            $this->logger->error("Cancellation Refund Error for Order {$incrementId}: " . $e->getMessage());
        }
    }
}
