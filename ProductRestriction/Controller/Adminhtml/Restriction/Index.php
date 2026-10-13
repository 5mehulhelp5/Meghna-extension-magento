<?php
namespace Codilar\ProductRestriction\Controller\Adminhtml\Restriction;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public function __construct(
        Context $context,
        protected readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codilar_ProductRestriction::restriction_list');
        $resultPage->getConfig()->getTitle()->prepend(__('Product Restrictions'));
        return $resultPage;
    }
}
