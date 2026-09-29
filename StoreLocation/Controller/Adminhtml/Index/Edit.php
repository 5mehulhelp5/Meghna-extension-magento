<?php

declare(strict_types=1);

namespace Codilar\StoreLocation\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Codilar\StoreLocation\Model\StoreFactory;
use Codilar\StoreLocation\Model\ResourceModel\Store as StoreResource;

class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_StoreLocation::store_location';

    public function __construct(
        Context $context,
        protected readonly PageFactory $resultPageFactory,
        protected readonly StoreFactory $storeFactory,
        protected readonly StoreResource $storeResource
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('store_id');
        $model = $this->storeFactory->create();

        if ($id) {
            $this->storeResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This store location no longer exists.'));
                return $this->_redirect('*/*/index');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codilar_StoreLocation::store_location');

        // Dynamic Title configuration
        $resultPage->getConfig()->getTitle()->prepend(__('Store Locations'));
        $title = $model->getId()
            ? __('Edit Store Location: %1', $model->getName())
            : __('New Store Location');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
