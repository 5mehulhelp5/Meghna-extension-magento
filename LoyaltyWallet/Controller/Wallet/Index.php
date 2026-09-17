<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Controller\Wallet;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\Session;

class Index implements HttpGetActionInterface
{
    protected PageFactory $resultPageFactory;
    protected Session $customerSession;
    protected RedirectFactory $resultRedirectFactory;

    public function __construct(
        PageFactory $resultPageFactory,
        Session $customerSession,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath('customer/account/login');
            return $redirect;
        }

        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set(__('My Store Wallet'));
        return $page;
    }
}
