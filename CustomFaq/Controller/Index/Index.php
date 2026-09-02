<?php
namespace Codilar\CustomFaq\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index implements HttpGetActionInterface
{
    protected $resultFactory;

    public function __construct(ResultFactory $resultFactory)
    {
        $this->resultFactory = $resultFactory; // Fixed missing $this->
    }

    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
