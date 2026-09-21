<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Observer;

use Codilar\LoyaltyWallet\Api\Data\LoyaltyLedgerInterfaceFactory;
use Codilar\LoyaltyWallet\Api\LoyaltyLedgerRepositoryInterface;
use Codilar\LoyaltyWallet\Logger\Logger as WalletLogger;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class AwardPointsOnOrderPlace implements ObserverInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LoyaltyLedgerRepositoryInterface $ledgerRepository,
        private readonly LoyaltyLedgerInterfaceFactory $ledgerFactory,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly WalletLogger $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        try {
            $order = $observer->getEvent()->getOrder();

            if (!$order || !$order->getCustomerId()) {
                $this->logger->info(
                    'Loyalty: Skipped (Guest order or missing order object).'
                );
                return;
            }

            $storeId = (int)$order->getStoreId();
            $subtotal = (float)$order->getSubtotal();
            $customerId = (int)$order->getCustomerId();

            if ($subtotal <= 0) {
                $this->logger->info(
                    'Loyalty: Skipped (Subtotal is zero or negative).'
                );
                return;
            }

            // Get points earning rate from store configuration
            $rate = (float)$this->scopeConfig->getValue(
                'codilar_loyalty/general/points_per_currency',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $pointsEarned = (int)floor($subtotal * $rate);

            if ($pointsEarned <= 0) {
                $this->logger->info(
                    "Loyalty: Skipped. Calculated points <= 0. " .
                    "Subtotal: {$subtotal}, Rate: {$rate}"
                );
                return;
            }

            /*
             * Get customer's latest loyalty ledger entry.
             */
            $sortOrder = $this->sortOrderBuilder
                ->setField('entity_id')
                ->setDescendingDirection()
                ->create();

            $searchCriteria = $this->searchCriteriaBuilderFactory
                ->create()
                ->addFilter('customer_id', $customerId)
                ->addSortOrder($sortOrder)
                ->setPageSize(1)
                ->create();

            $transactions = $this->ledgerRepository
                ->getList($searchCriteria)
                ->getItems();

            $latestRecord = reset($transactions);

            /*
             * Get previous balance.
             */
            $currentBalance = $latestRecord
                ? (int)$latestRecord->getBalanceAfter()
                : 0;

            /*
             * Add newly earned points.
             */
            $newBalanceAfter = $currentBalance + $pointsEarned;

            /*
             * Create a new immutable ledger entry.
             */
            $ledger = $this->ledgerFactory->create();

            $ledger->setCustomerId($customerId);
            $ledger->setOrderIncrementId(
                (string)$order->getIncrementId()
            );
            $ledger->setQuantity($pointsEarned);
            $ledger->setBalanceAfter($newBalanceAfter);

            $this->ledgerRepository->save($ledger);

            $this->logger->info(
                'Loyalty: Successfully awarded ' .
                $pointsEarned .
                ' points for order ' .
                $order->getIncrementId() .
                '. New balance: ' .
                $newBalanceAfter
            );

        } catch (\Exception $e) {

            /*
             * Loyalty point failure should not break checkout.
             */
            $this->logger->error(
                'Loyalty Accrual Exception: ' .
                $e->getMessage()
            );
        }
    }
}
