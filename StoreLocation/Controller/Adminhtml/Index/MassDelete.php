<?php

namespace Codilar\StoreLocation\Controller\Adminhtml\Index;

use Codilar\StoreLocation\Api\StoreRepositoryInterface;
use Codilar\StoreLocation\Model\ResourceModel\Store\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'Codilar_StoreLocation::store_location';

    public function __construct(
        Context $context,
        protected readonly Filter $filter,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly StoreRepositoryInterface $storeRepository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );

        $singleId = $this->getRequest()->getParam('store_id');

        if ($singleId) {
            try {
                $this->storeRepository->deleteById((int) $singleId);

                $this->messageManager->addSuccessMessage(
                    __('The store location has been deleted.')
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    $e->getMessage()
                );
            }

            return $resultRedirect->setPath('*/*/index');
        }

        try {
            $collection = $this->filter->getCollection(
                $this->collectionFactory->create()
            );

            $storeIds = [];

            foreach ($collection as $store) {
                $storeIds[] = (int) $store->getId();
            }

            $this->storeRepository->deleteByIds($storeIds);


            $count = count($storeIds);
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $count)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
