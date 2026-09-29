<?php

declare(strict_types=1);

namespace Codilar\StoreLocation\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Codilar\StoreLocation\Model\ImageUploader;
use Codilar\StoreLocation\Model\StoreFactory;
use Codilar\StoreLocation\Model\ResourceModel\Store as StoreResource;
use Psr\Log\LoggerInterface;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_StoreLocation::store_location';

    public function __construct(
        Context $context,
        private readonly ImageUploader $imageUploader,
        private readonly StoreFactory $storeFactory,
        private readonly StoreResource $storeResource,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();
        $data = $request->getPostValue();

        $this->logger->info('StoreLocation Raw POST Data: ' . print_r($data, true));

        if (!$data) {
            $this->messageManager->addErrorMessage(__('No data to save.'));
            return $this->_redirect('*/*/index');
        }

        try {
            // Since your fields are at the root level of the POST array, use $data directly
            $formData = $data;

            // If nested under general or data, extract them
            foreach (['general', 'data', 'codilar_storelocation_form'] as $key) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    $formData = $data[$key];
                    break;
                }
            }

            $model = $this->storeFactory->create();
            $id = !empty($formData['store_id']) ? (int)$formData['store_id'] : null;

            if ($id) {
                $this->storeResource->load($model, $id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This store location no longer exists.'));
                    return $this->_redirect('*/*/index');
                }
            }

            // Handle Image Upload saving from tmp to permanent folder safely
            if (isset($formData['image']) && is_array($formData['image'])) {
                $imageVal = $formData['image'][0]['name'] ?? null;
                if ($imageVal) {
                    try {
                        if (isset($formData['image'][0]['tmp_name']) || str_contains($imageVal, 'tmp')) {
                            $formData['image'] = $this->imageUploader->moveFileFromTmp($imageVal);
                        } else {
                            $formData['image'] = $imageVal;
                        }
                    } catch (\Exception $imgEx) {
                        $this->logger->error('Image Upload Error: ' . $imgEx->getMessage());
                        $formData['image'] = null;
                    }
                } else {
                    $formData['image'] = null;
                }
            } else {
                $formData['image'] = null;
            }

            // Clean up unwanted parameters
            unset($formData['form_key']);

            // Filter out empty string store_id so Magento creates a new auto-increment row
            if (empty($formData['store_id'])) {
                unset($formData['store_id']);
            }

            // Set data and save to database resource model
            $model->setData($formData);
            $this->storeResource->save($model);

            $this->logger->info('StoreLocation Successfully Saved with Database ID: ' . $model->getId());

            $this->messageManager->addSuccessMessage(__('You saved the store location.'));
            $this->_getSession()->setFormData(false);

            if ($this->getRequest()->getParam('back')) {
                return $this->_redirect('*/*/edit', ['store_id' => $model->getId(), '_current' => true]);
            }

            return $this->_redirect('*/*/index');
        } catch (\Throwable $e) {
            $this->logger->error('StoreLocation Save Critical Exception: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the store location: %1', $e->getMessage()));
            $this->_getSession()->setFormData($data);
            return $this->_redirect('*/*/edit', ['store_id' => $this->getRequest()->getParam('store_id')]);
        }
    }
}
