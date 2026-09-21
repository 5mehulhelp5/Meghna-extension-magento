<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Block\Checkout;

use Codilar\LoyaltyWallet\Api\LoyaltyLedgerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;

class WalletApply extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly CustomerSession $customerSession,
        private readonly LoyaltyLedgerRepositoryInterface $ledgerRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly ResourceConnection $resourceConnection,
        private readonly ?LoggerInterface $logger = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getCustomerBalance(): float
    {
        if (!$this->customerSession->isLoggedIn()) {
            return 0.0;
        }

        $customerId = (int)$this->customerSession->getCustomerId();

        try {
            $connection = $this->resourceConnection->getConnection();

            $tableName = $this->resourceConnection->getTableName(
                'codilar_store_wallet_ledger'
            );

            $select = $connection->select()
                ->from($tableName, ['balance_after'])
                ->where('customer_id = ?', $customerId)
                ->order('entity_id DESC')
                ->limit(1);

            $balance = $connection->fetchOne($select);

            return $balance !== false ? (float)$balance : 0.0;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error(
                    'Unable to fetch wallet balance: ' . $e->getMessage()
                );
            }

            return 0.0;
        }
    }
}
