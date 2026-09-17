<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use Codilar\LoyaltyWallet\Api\StoreWalletRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Psr\Log\LoggerInterface;

class StoreWallet extends Template
{
    protected CustomerSession $customerSession;
    protected StoreWalletRepositoryInterface $storeWalletRepository;
    protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;
    protected SortOrderBuilder $sortOrderBuilder;
    protected LoggerInterface $logger;

    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        StoreWalletRepositoryInterface $storeWalletRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SortOrderBuilder $sortOrderBuilder,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->storeWalletRepository = $storeWalletRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->logger = $logger;
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
