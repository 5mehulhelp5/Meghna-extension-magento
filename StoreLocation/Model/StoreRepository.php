<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Model;

use Codilar\StoreLocation\Api\Data\StoreApiResponseInterface;
use Codilar\StoreLocation\Api\Data\StoreApiResponseInterfaceFactory;
use Codilar\StoreLocation\Api\Data\StoreInterface;
use Codilar\StoreLocation\Api\Data\StoreInterfaceFactory;
use Codilar\StoreLocation\Api\StoreRepositoryInterface;
use Codilar\StoreLocation\Model\ResourceModel\Store as StoreResource;
use Codilar\StoreLocation\Model\ResourceModel\Store\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Webapi\Rest\Response as RestResponse;

class StoreRepository implements StoreRepositoryInterface
{
    /**
     * @param StoreResource $resource
     * @param StoreInterfaceFactory $storeFactory
     * @param StoreApiResponseInterfaceFactory $apiResponseFactory
     * @param Filesystem $filesystem
     * @param RestResponse $restResponse
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private readonly StoreResource $resource,
        private readonly StoreInterfaceFactory $storeFactory,
        private readonly StoreApiResponseInterfaceFactory $apiResponseFactory,
        private readonly Filesystem $filesystem,
        private readonly RestResponse $restResponse,
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @param StoreInterface $store
     * @return StoreApiResponseInterface
     * @throws CouldNotSaveException
     */
    public function save(StoreInterface $store): StoreApiResponseInterface
    {
        $response = $this->apiResponseFactory->create();

        try {

            $imageData = $store->getImage();

            if ($imageData && str_starts_with($imageData, 'data:image')) {
                $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                $parts = explode(',', $imageData);
                if (count($parts) < 2 || empty($parts[1])) {
                    $this->restResponse->setHttpResponseCode(400);
                    $response->setStatus('error');
                    $response->setMessage('Invalid base64 image payload: Missing metadata comma separator.');
                    $response->setData($store);
                    return $response;
                }

                $meta = $parts[0];
                $data = $parts[1];

                preg_match('/image\/([a-zA-Z0-9\+]+)/', $meta, $matches);
                $extension = $matches[1] ?? 'jpg';
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }
                $imageDecoded = base64_decode($data, true);
                if ($imageDecoded === false) {
                    $this->restResponse->setHttpResponseCode(400);
                    $response->setStatus('error');
                    $response->setCode(400);
                    $response->setMessage('Invalid base64 image encoding.');
                    $response->setData([$store]);
                    return $response;
                }

                $filename = 'store_' . uniqid('', true) . '.' . $extension;
                $filePath = 'store_locations/' . $filename;
                $mediaDirectory->writeFile($filePath, $imageDecoded);
                $store->setImage($filePath);
            }

            $isNew = !$store->getStoreId();
            $statusCode = $isNew ? 201 : 200;

            $this->resource->save($store);

            // Set success status code
            $this->restResponse->setHttpResponseCode($statusCode);

            $response->setStatus('success');
            $response->setCode($statusCode);
            $response->setMessage($isNew ? 'Store location created successfully' : 'Store location updated successfully');
            $response->setData([$store]);

            return $response;

        } catch (\Throwable $e) {
            // Catch any unexpected server crashes or database errors -> Return 500
            $this->restResponse->setHttpResponseCode(500);

            $response->setStatus('error');
            $response->setMessage('Internal Server Error: ' . $e->getMessage());
            $response->setData([$store]);

            return $response;
        }
    }

    /**
     * @param int $storeId
     * @return StoreApiResponseInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $storeId): StoreApiResponseInterface
    {

        $store = $this->getById($storeId);

        try {
            $this->resource->delete($store);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __('Could not delete store location: %1', $e->getMessage())
            );
        }

        $response = $this->apiResponseFactory->create();
        $response->setStatus('success');
        $response->setCode(200);
        $response->setMessage('Store location deleted successfully.');
        $response->setData([]);

        return $response;
    }

    /**
     * @return StoreApiResponseInterface
     */
    public function getList(): StoreApiResponseInterface
    {
        $response = $this->apiResponseFactory->create();
        try {
            $collection = $this->collectionFactory->create();
            $items = $collection->getItems();

            $response->setStatus('success');
            $response->setMessage("Store locations fetched successfully.");
            $response->setData(array_values($items));
        } catch (\Exception $e) {
            $response->setStatus('error');
            $response->setMessage($e->getMessage());
            $response->setData([]);
        }

        return $response;
    }

    /**
     * @param int $storeId
     * @return StoreApiResponseInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $storeId): StoreApiResponseInterface
    {
        $store = $this->storeFactory->create();
        $this->resource->load($store, $storeId);

        if (!$store->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Store location with id  does not exist.', $storeId)
            );
        }

        $response = $this->apiResponseFactory->create();
        $response->setStatus('success');
        //        $response->setCode(200);
        $response->setMessage('Store location fetched successfully.');
        $response->setData([$store]);

        return $response;
    }
}
