<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Block\Customer;

use Codilar\LoyaltyWallet\Api\LoyaltyLedgerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;

class Coins extends Template
{
    private ?array $transactionsCache = null;

    public function __construct(
        Template\Context $context,
        private readonly CustomerSession $customerSession,
        private readonly LoyaltyLedgerRepositoryInterface $ledgerRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    public function getCustomerTransactions(): array
    {
        if ($this->transactionsCache !== null) {
            return $this->transactionsCache;
        }

        if (!$this->customerSession->isLoggedIn()) {
            return $this->transactionsCache = [];
        }

        $customerId = $this->customerSession->getCustomerId();

        try {
            $this->searchCriteriaBuilder->addFilter('customer_id', $customerId);

            // Sort by newest first so index 0 is always the latest transaction
            $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDescendingDirection()->create();
            $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);

            $searchCriteria = $this->searchCriteriaBuilder->create();
            $this->transactionsCache = $this->ledgerRepository->getList($searchCriteria)->getItems();
        } catch (\Exception $e) {
            $this->logger->error('Loyalty Dashboard Error: ' . $e->getMessage());
            $this->transactionsCache = [];
        }

        return $this->transactionsCache;
    }

    public function getTotalCoins(): int
    {
        $transactions = $this->getCustomerTransactions();

        $this->logger->info('getTotalCoins Called', [
            'transaction_count' => count($transactions)
        ]);

        if (empty($transactions)) {
            return 0;
        }

        // Sort by entity_id ascending so the highest/newest ID is at the end
        usort($transactions, function ($a, $b) {
            return (int)$a->getEntityId() <=> (int)$b->getEntityId();
        });

        // Log sorted entity IDs to verify order
        $sortedIds = array_map(function ($txn) {
            return $txn->getEntityId();
        }, $transactions);

        $this->logger->info('Transactions Sorted by Entity ID (Ascending)', [
            'sorted_entity_ids' => $sortedIds
        ]);

        $latestTransaction = end($transactions);
        $balanceAfter = $latestTransaction ? (int)$latestTransaction->getBalanceAfter() : 0;
        $finalBalance = max(0, $balanceAfter);

        $this->logger->info('Latest Transaction Balance Resolved', [
            'latest_entity_id' => $latestTransaction ? $latestTransaction->getEntityId() : null,
            'balance_after' => $balanceAfter,
            'final_returned_balance' => $finalBalance
        ]);

        return $finalBalance;
    }
}
