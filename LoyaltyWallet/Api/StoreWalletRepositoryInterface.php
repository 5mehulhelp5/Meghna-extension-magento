<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Api;

use Codilar\LoyaltyWallet\Api\Data\StoreWalletInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface StoreWalletRepositoryInterface
{
    /**
     * @param StoreWalletInterface $wallet
     * @return StoreWalletInterface
     */
    public function save(StoreWalletInterface $wallet): StoreWalletInterface;

    /**
     * @param int $entityId
     * @return StoreWalletInterface
     */

    public function getById(int $entityId): StoreWalletInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */

    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param StoreWalletInterface $wallet
     * @return bool
     */
    public function delete(StoreWalletInterface $wallet): bool;
}
