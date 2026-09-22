<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Block\Checkout;

use Codilar\LoyaltyWallet\Api\StoreWalletRepositoryInterface;
use Codilar\LoyaltyWallet\Logger\Logger as WalletLogger;
use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\View\Element\Template;

class WalletApply extends Template
{
    public function __construct(Template\Context $context, private readonly CustomerSession $customerSession, private readonly StoreWalletRepositoryInterface $storeWalletRepository, private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory, private readonly SortOrderBuilder $sortOrderBuilder, private readonly WalletLogger $logger, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @return float
     */

    public function getCustomerBalance(): float
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->logger->info('WalletApply: Customer is not logged in.');

            return 0.0;
        }

        $customerId = (int)$this->customerSession->getCustomerId();

        try {
            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

            $searchCriteriaBuilder->addFilter('customer_id', $customerId);

            $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDescendingDirection()->create();

            $searchCriteriaBuilder->setSortOrders([$sortOrder]);

            $searchCriteriaBuilder->setPageSize(1);

            $result = $this->storeWalletRepository->getList($searchCriteriaBuilder->create());

            $transactions = $result->getItems();

            if (empty($transactions)) {
                $this->logger->info('WalletApply: No wallet transactions found for customer ID: ' . $customerId);

                return 0.0;
            }

            $latestTransaction = reset($transactions);

            $balance = max(0.0, (float)$latestTransaction->getBalanceAfter());

            $this->logger->info('WalletApply: Wallet balance fetched successfully. ' . 'Customer ID: ' . $customerId . ', Balance: ' . $balance);

            return $balance;

        } catch (Exception $e) {

            $this->logger->error('WalletApply: Unable to fetch wallet balance. ' . 'Customer ID: ' . $customerId . ', Error: ' . $e->getMessage());

            return 0.0;
        }
    }
}
