<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Api;

interface LoyaltyLedgerRepositoryInterface
{
    /**
     * @param LoyaltyLedgerInterface $ledger
     * @return LoyaltyLedgerInterface
     */
    public function save(\Codilar\LoyaltyWallet\Api\Data\LoyaltyLedgerInterface $ledger): \Codilar\LoyaltyWallet\Api\Data\LoyaltyLedgerInterface;
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): \Magento\Framework\Api\SearchResultsInterface;
}
