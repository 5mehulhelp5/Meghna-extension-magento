<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Api;

use Codilar\LoyaltyWallet\Api\Data\StoreWalletInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface StoreWalletRepositoryInterface
{
    public function save(StoreWalletInterface $wallet): StoreWalletInterface;

    public function getById(int $entityId): StoreWalletInterface;

    public function getList(SearchCriteriaInterface $searchCriteria);

    public function delete(StoreWalletInterface $wallet): bool;
}
