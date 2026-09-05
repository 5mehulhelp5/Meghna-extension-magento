<?php
declare(strict_types=1);

namespace Codilar\ProductEnquiry\Controller\Index;

use Codilar\ProductEnquiry\Api\Data\EnquiryInterfaceFactory;
use Codilar\ProductEnquiry\Api\EnquiryRepositoryInterface;
use Codilar\ProductEnquiry\Logger\Logger;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class Submit implements HttpPostActionInterface
{
    /**
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param EnquiryInterfaceFactory $enquiryFactory
     * @param EnquiryRepositoryInterface $enquiryRepository
     * @param Logger $logger
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $jsonFactory,
        private readonly EnquiryInterfaceFactory $enquiryFactory,
        private readonly EnquiryRepositoryInterface $enquiryRepository,
        private readonly Logger $logger
    ) {
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->jsonFactory->create();

        try {
            $postData = $this->request->getPostValue();
            if (empty($postData)) {
                throw new \InvalidArgumentException((string)__('Invalid request data.'));
            }

            // Extract and trim values
            $name = trim($postData['name'] ?? '');
            $address = trim($postData['address'] ?? '');
            $email = trim($postData['email'] ?? '');
            $sku = trim($postData['sku'] ?? '');
            $qty = (int) ($postData['qty'] ?? 0);

            // Server-side validation for empty fields
            if ($name === '' || $address === '' || $email === '' || $sku === '' || $qty <= 0) {
                throw new \InvalidArgumentException((string)__('All required fields must be filled and quantity must be greater than zero.'));
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException((string)__('Please enter a valid email address.'));
            }

            $enquiry = $this->enquiryFactory->create();
            $enquiry->setName($name);
            $enquiry->setAddress($address);
            $enquiry->setEmail($email);
            $enquiry->setSku($sku);
            $enquiry->setQty($qty);

            $this->enquiryRepository->save($enquiry);

            $this->logger->info(sprintf('Product enquiry saved successfully for SKU: %s by %s', $enquiry->getSku(), $enquiry->getEmail()));

            return $resultJson->setData(['success' => true, 'message' => __('Enquiry submitted successfully.')]);
        } catch (\Throwable $e) {
            $this->logger->error('Error saving product enquiry: ' . $e->getMessage());
            return $resultJson->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
