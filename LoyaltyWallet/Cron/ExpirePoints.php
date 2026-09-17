<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Cron;

use Codilar\LoyaltyWallet\Api\Data\LoyaltyLedgerInterfaceFactory;
use Codilar\LoyaltyWallet\Api\LoyaltyLedgerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Psr\Log\LoggerInterface;

class ExpirePoints
{
    public function __construct(
        private readonly LoyaltyLedgerInterfaceFactory $ledgerFactory,
        private readonly LoyaltyLedgerRepositoryInterface $ledgerRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        try {
            // Fetch all ledger entries sorted chronologically (FIFO: oldest first)
            $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setAscendingDirection()->create();
            $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);
            $entries = $this->ledgerRepository->getList($this->searchCriteriaBuilder->create())->getItems();

            // Group entries by customer
            $customerEntries = [];
            foreach ($entries as $entry) {
                $customerEntries[$entry->getCustomerId()][] = $entry;
            }

            // Define the 12-month expiry threshold limit date
            $expiryLimit = (new \DateTime())->modify('-12 months');

            foreach ($customerEntries as $customerId => $records) {
                // Simulate FIFO pool consumption for this customer
                $activePools = [];

                foreach ($records as $record) {
                    $qty = (int)$record->getQuantity();
                    $comment = (string)$record->getComment();

                    if ($qty > 0) {
                        // Earned points pool
                        $activePools[] = [
                            'entity_id' => $record->getEntityId(),
                            'order_id' => $record->getOrderIncrementId(),
                            'remaining' => $qty,
                            'created_at' => $record->getCreatedAt()
                        ];
                    } elseif ($qty < 0) {
                        // Spent or expired points deplete the oldest active pools first (FIFO)
                        $debitRemaining = abs($qty);
                        foreach ($activePools as &$pool) {
                            if ($debitRemaining <= 0) {
                                break;
                            }
                            if ($pool['remaining'] > 0) {
                                $deduct = min($pool['remaining'], $debitRemaining);
                                $pool['remaining'] -= $deduct;
                                $debitRemaining -= $deduct;
                            }
                        }
                        unset($pool);
                    }
                }

                // Check remaining active pools against the 12-month expiration threshold
                foreach ($activePools as $pool) {
                    if ($pool['remaining'] <= 0) {
                        continue;
                    }

                    $createdAt = new \DateTime($pool['created_at']);
                    if ($createdAt <= $expiryLimit) {
                        // Check if an expiration entry for this specific pool already exists
                        $isAlreadyExpired = false;
                        foreach ($records as $rec) {
                            if ($rec->getOrderIncrementId() === $pool['order_id']
                                && (int)$rec->getQuantity() === -($pool['remaining'])
                                && $rec->getComment() === 'Points Expired') {
                                $isAlreadyExpired = true;
                                break;
                            }
                        }

                        if (!$isAlreadyExpired) {
                            $expirationQty = -$pool['remaining'];

                            /** @var \Codilar\LoyaltyWallet\Model\Data\LoyaltyLedger $ledger */
                            $ledger = $this->ledgerFactory->create();
                            $ledger->setCustomerId((int)$customerId);
                            $ledger->setOrderIncrementId($pool['order_id']);
                            $ledger->setQuantity($expirationQty);
                            $ledger->setComment('Points Expired');

                            // Calculate current running balance from repository items
                            $allCurrent = $this->ledgerRepository->getList(
                                $this->searchCriteriaBuilder->addFilter('customer_id', $customerId)->create()
                            )->getItems();

                            $runningBalance = 0;
                            foreach ($allCurrent as $item) {
                                $runningBalance += (int)$item->getQuantity();
                            }

                            // Ensure balance after expiration never drops below 0
                            $newBalance = $runningBalance + $expirationQty;
                            $ledger->setBalanceAfter(max(0, $newBalance));
                            $ledger->setCreatedAt(date('Y-m-d H:i:s'));

                            // Save discrete negative ledger entry for expiration
                            $this->ledgerRepository->save($ledger);
                            $this->logger->info("Loyalty: Expired {$pool['remaining']} points for customer {$customerId} (Order: {$pool['order_id']}) via FIFO.");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("Loyalty Expiration Cron Error: " . $e->getMessage());
        }
    }
}
