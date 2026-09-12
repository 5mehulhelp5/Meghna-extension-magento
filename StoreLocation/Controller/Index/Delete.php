<?php
namespace Codilar\StoreLocation\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Codilar\StoreLocation\Model\StoreRepository;

class Delete implements HttpGetActionInterface
{
    protected $request;
    protected $storeRepository;
    protected $resultFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        StoreRepository $storeRepository,
        ResultFactory $resultFactory
    ) {
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        $storeId = $this->request->getParam('id');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            if ($storeId) {
                $this->storeRepository->deleteById($storeId);
                // MessageManager success notification can be added here
            }
        } catch (\Exception $e) {
            // Handle exception
        }

        return $resultRedirect->setPath('stores/index/index');
    }
}
