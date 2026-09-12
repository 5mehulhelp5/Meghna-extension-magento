<?php
namespace Codilar\MyApi\Model;

use Codilar\MyApi\Api\HelloInterface;

class Hello implements HelloInterface
{
    /**
     * @inheritdoc
     */
    public function getGreeting($name)
    {
        return "Hello, " . htmlspecialchars($name) . "! Welcome to Magento REST API.";
    }
}
