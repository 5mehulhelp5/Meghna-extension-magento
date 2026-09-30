<?php

declare(strict_types=1);

namespace Codilar\StoreLocation\Controller\Adminhtml\Index;

use Codilar\StoreLocation\Model\ImageUploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Throwable;

class Upload extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_StoreLocation::store_location';

    public function __construct(Context $context, private readonly JsonFactory $jsonFactory, private readonly ImageUploader $imageUploader, private readonly LoggerInterface $logger)
    {
        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        try {

            $result = $this->imageUploader->saveFileToTmpDir('image');

            if (!$result) {
                throw new LocalizedException(__('Something went wrong while saving the file.'));
            }

            $result['cookie'] = ['name' => $this->_getSession()->getName(), 'value' => $this->_getSession()->getSessionId(), 'lifetime' => (int)$this->_getSession()->getCookieLifetime(), 'path' => $this->_getSession()->getCookiePath(), 'domain' => $this->_getSession()->getCookieDomain()];
        } catch (Throwable $e) {
            $this->logger->error('StoreLocation Upload Error: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());

            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode(), 'file' => $e->getFile(), 'line' => $e->getLine()];
        }

        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData($result);
    }
}
