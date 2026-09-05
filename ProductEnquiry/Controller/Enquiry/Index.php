<?php
declare(strict_types=1);

namespace Codilar\ProductEnquiry\Controller\Enquiry;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class Index implements HttpGetActionInterface
{
    /**
     * @param ResultFactory $resultFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ResultFactory $resultFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return ResultInterface
     * @throws \Throwable
     */
    public function execute(): ResultInterface
    {
        try {
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->set(__('Product Enquiry History'));
            return $resultPage;
        } catch (\Throwable $e) {
            $this->logger->error('Codilar Enquiry History Controller Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            // Return a simple text response or let Magento handle it, but now it's logged!
            throw $e;
        }
    }
}
