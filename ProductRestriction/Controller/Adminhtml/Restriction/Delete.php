<?php
namespace Codilar\ProductRestriction\Controller\Adminhtml\Restriction;

use Codilar\ProductRestriction\Api\ProductRestrictionRepositoryInterface;
use Magento\Backend\App\Action;

class Delete extends Action
{

    public function __construct(
        Action\Context $context,
        protected readonly ProductRestrictionRepositoryInterface $productRestrictionRepository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->productRestrictionRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the restriction.'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can not find a restriction to delete.'));
        return $resultRedirect->setPath('*/*/index');
    }
}
