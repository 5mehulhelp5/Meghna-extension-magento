<?php
namespace Codilar\ProductRestriction\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action;
use Codilar\ProductRestriction\Api\ProductRestrictionRepositoryInterface;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{

    public function __construct(
        Action\Context $context,
        protected readonly PageFactory $resultPageFactory,
        protected readonly ProductRestrictionRepositoryInterface $productRestrictionRepository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->_objectManager->create(\Codilar\ProductRestriction\Model\ProductRestriction::class);

        if ($id) {
            try {
                $model = $this->productRestrictionRepository->getById($id);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('This restriction no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codilar_ProductRestriction::restriction_list');
        $resultPage->getConfig()->getTitle()->prepend(__('Product Restrictions'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Restriction #%1', $model->getId()) : __('New Restriction'));
        return $resultPage;
    }
}
