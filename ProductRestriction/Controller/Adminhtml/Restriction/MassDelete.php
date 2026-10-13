<?php
namespace Codilar\ProductRestriction\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction\CollectionFactory;

class MassDelete extends Action
{
    public function __construct(
        Context $context,
        protected readonly Filter $filter,
        protected readonly CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $deleteCount = 0;

            foreach ($collection as $item) {
                $item->delete();
                $deleteCount++;
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $deleteCount)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
