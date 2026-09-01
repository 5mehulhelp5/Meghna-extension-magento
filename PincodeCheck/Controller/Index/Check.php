<?php
namespace Codilar\PincodeCheck\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 */
class Check implements HttpPostActionInterface
{
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly JsonFactory $resultJsonFactory
    ) {
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $resultJson = $this->resultJsonFactory->create();
        $pincode = $this->request->getParam('pincode');

        $validPincodes = ['110001', '400001', '500001', '700001', '700125'];
        $isAvailable = in_array(trim((string)$pincode), $validPincodes, true);

        return $resultJson->setData([
            'success' => true,
            'is_available' => $isAvailable,
            'expected_days' => '3–5 days'
        ]);
    }
}
