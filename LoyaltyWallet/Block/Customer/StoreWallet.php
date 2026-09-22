<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Block\Customer;

use Codilar\LoyaltyWallet\Api\StoreWalletRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\View\Element\Template;
use Codilar\LoyaltyWallet\Logger\Logger as WalletLogger;

class StoreWallet extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly CustomerSession $customerSession,
        private readonly StoreWalletRepositoryInterface $storeWalletRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly WalletLogger $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getCustomerId(): ?int
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }
        return (int)$this->customerSession->getCustomerId();
    }

    /**
     * Fetch full transaction history for Store Cash Wallet
     */
    public function getStoreWalletTransactions(): array
    {
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return [];
        }

        try {
            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
            $searchCriteriaBuilder->addFilter('customer_id', $customerId);

            $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDescendingDirection()->create();
            $searchCriteriaBuilder->setSortOrders([$sortOrder]);

            $result = $this->storeWalletRepository->getList($searchCriteriaBuilder->create());
            return $result->getItems();
        } catch (\Exception $e) {
            $this->logger->error('Error fetching store wallet transactions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get current overall Store Cash Wallet Balance
     */
    public function getStoreWalletBalance(): float
    {
        $transactions = $this->getStoreWalletTransactions();
        if (empty($transactions)) {
            return 0.00;
        }
        $latest = reset($transactions);
        return max(0.0, (float)$latest->getBalanceAfter());
    }
}
