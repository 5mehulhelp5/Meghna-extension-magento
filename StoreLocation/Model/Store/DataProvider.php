<?php

declare(strict_types=1);

namespace Codilar\StoreLocation\Model\Store;

use Codilar\StoreLocation\Model\ResourceModel\Store\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    private ?array $loadedData = null;
    private DataPersistorInterface $dataPersistor;
    private RequestInterface $request;
    private StoreManagerInterface $storeManager;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $collection = $this->getCollection();

        foreach ($collection as $store) {
            $storeData = $store->getData();

            // Transform raw image file name into the array format required by imageUploader UI component
            if (!empty($storeData['image']) && is_string($storeData['image'])) {
                $imageName = $storeData['image'];
                $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'store_locations/image/';
                $filePath = $this->storeManager->getStore()->getBaseDir(UrlInterface::URL_TYPE_MEDIA) . '/store_locations/image/' . $imageName;

                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

                $storeData['image'] = [
                    [
                        'name' => $imageName,
                        'url' => $mediaUrl . $imageName,
                        'size' => $fileSize,
                        'status' => 'old',
                        'type' => 'image'
                    ]
                ];
            }

            $this->loadedData[$store->getId()] = $storeData;
        }

        $persisted = $this->dataPersistor->get('store_location');
        if ($persisted) {
            $key = $persisted['store_id'] ?? null;
            if ($key) {
                $this->loadedData[$key] = $persisted;
            } else {
                $this->loadedData[0] = $persisted;
            }
            $this->dataPersistor->clear('store_location');
        }

        // Ensure an empty structure exists for new items so JS bindings don't fail
        if (empty($this->loadedData)) {
            $emptyItem = $this->collection->getNewEmptyItem();
            $this->loadedData[$emptyItem->getId()] = [];
        }

        return $this->loadedData;
    }
}
