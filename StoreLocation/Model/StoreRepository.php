<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Model;

use Codilar\StoreLocation\Api\Data\StoreInterface;
use Codilar\StoreLocation\Api\Data\StoreInterfaceFactory;
use Codilar\StoreLocation\Api\StoreRepositoryInterface;
use Codilar\StoreLocation\Model\ResourceModel\Store as StoreResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class StoreRepository implements StoreRepositoryInterface
{
    /**
     * @param StoreResource $resource
     * @param StoreInterfaceFactory $storeFactory
     */
    public function __construct(
        private readonly StoreResource $resource,
        private readonly StoreInterfaceFactory $storeFactory
    ) {
    }

    /**
     * @param StoreInterface $store
     * @return StoreInterface
     * @throws CouldNotSaveException
     */
    public function save(StoreInterface $store): StoreInterface
    {
        try {
            $this->resource->save($store);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(__('Could not save store location.'), $e);
        }
        return $store;
    }

    /**
     * @param int $storeId
     * @return StoreInterface
     * @throws NoSuchEntityException
     */

    public function getById(int $storeId): StoreInterface
    {
        $store = $this->storeFactory->create();
        $this->resource->load($store, $storeId);
        if (!$store->getStoreId()) {
            throw new NoSuchEntityException(__('Store with ID "%1" does not exist.', $storeId));
        }
        return $store;
    }

    public function deleteById(int $storeId): bool
    {
        // TODO: Implement deleteById() method.
    }
}
