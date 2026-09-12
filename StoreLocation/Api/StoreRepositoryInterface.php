<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Api;

use Codilar\StoreLocation\Api\Data\StoreApiResponseInterface;
use Codilar\StoreLocation\Api\Data\StoreInterface;

interface StoreRepositoryInterface
{
    /**
     * @param StoreInterface $store
     * @return \Codilar\StoreLocation\Api\Data\StoreApiResponseInterface
     */
    public function save(StoreInterface $store): StoreApiResponseInterface;
    /**
     * Get store location by ID.
     *
     * @param int $storeId
     * @return \Codilar\StoreLocation\Api\Data\StoreApiResponseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $storeId): StoreApiResponseInterface;
    /**
     * Delete store location by ID.
     *
     * @param int $storeId
     * @return \Codilar\StoreLocation\Api\Data\StoreApiResponseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $storeId): StoreApiResponseInterface;

    /**
     * @return \Codilar\StoreLocation\Api\Data\StoreApiResponseInterface
     */
    public function getList(): StoreApiResponseInterface;

}
