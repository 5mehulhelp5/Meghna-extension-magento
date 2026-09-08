<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Api;

use Codilar\StoreLocation\Api\Data\StoreInterface;

interface StoreRepositoryInterface
{
    /**
     * @param StoreInterface $store
     * @return StoreInterface
     */
    public function save(StoreInterface $store): StoreInterface;

    /**
     * @param int $storeId
     * @return StoreInterface
     */
    public function getById(int $storeId): StoreInterface;

    /**
     * @param int $storeId
     * @return bool
     */
    public function deleteById(int $storeId): bool;
}
