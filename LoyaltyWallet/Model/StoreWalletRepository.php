<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model;

use Codilar\LoyaltyWallet\Api\StoreWalletRepositoryInterface;
use Codilar\LoyaltyWallet\Api\Data\StoreWalletInterface;
use Codilar\LoyaltyWallet\Api\Data\StoreWalletInterfaceFactory;
use Codilar\LoyaltyWallet\Model\ResourceModel\StoreWallet as ResourceStoreWallet;
use Codilar\LoyaltyWallet\Model\ResourceModel\StoreWallet\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class StoreWalletRepository implements StoreWalletRepositoryInterface
{
    protected ResourceStoreWallet $resource;
    protected StoreWalletInterfaceFactory $storeWalletFactory;
    protected CollectionFactory $collectionFactory;
    protected CollectionProcessorInterface $collectionProcessor;

    public function __construct(
        ResourceStoreWallet $resource,
        StoreWalletInterfaceFactory $storeWalletFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->storeWalletFactory = $storeWalletFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(StoreWalletInterface $wallet): StoreWalletInterface
    {
        try {
            $this->resource->save($wallet);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $wallet;
    }

    public function getById(int $entityId): StoreWalletInterface
    {
        $wallet = $this->storeWalletFactory->create();
        $this->resource->load($wallet, $entityId);
        if (!$wallet->getId()) {
            throw new NoSuchEntityException(__('Store wallet record with ID "%1" does not exist.', $entityId));
        }
        return $wallet;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        // If your data interface includes aSearchResultsInterface factory, you can map it,
        // otherwise returning the collection items/collection directly is handled by Magento collection processor.
        return $collection;
    }

    public function delete(StoreWalletInterface $wallet): bool
    {
        try {
            $this->resource->delete($wallet);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }
}
