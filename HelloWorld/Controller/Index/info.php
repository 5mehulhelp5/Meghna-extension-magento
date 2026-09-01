<?php
namespace Codilar\HelloWorld\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class Info implements ActionInterface
{
    public function __construct(private readonly JsonFactory $jsonFactory, private readonly RequestInterface $request)
    {
    }

    public function execute(): ResultInterface
    {
        $name = $this->request->getParam('name', 'Guest');
        $result = $this->jsonFactory->create();
        return $result->setData([
            'name' => $name,
            "role" => "Magento Developer",
            "learning" => "Magento 2"
        ]);
    }
}
