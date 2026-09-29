<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Controller\Adminhtml\Index;

use Codilar\StoreLocation\Api\StoreRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;

class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'StoreLocation_StoreLocation::store_location';

    public function __construct(
        Context $context,
        private readonly StoreRepositoryInterface $repository
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int) $this->getRequest()->getParam('store_id');

        if ($id) {
            try {
                $this->repository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The store location was deleted.'));
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage(__('The store location could not be deleted.'));
                return $resultRedirect->setPath('*/*/edit', ['store_id' => $id]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
