<?php

namespace Codilar\CustomerLogin\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Codilar\CustomerLogin\Logger\Logger;

class CustomerLoginObserver implements ObserverInterface
{

    /**
     * @param Logger $logger
     */
    public function __construct(
        public readonly Logger $logger
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        $customerId = $customer->getId();
        $email = $customer->getEmail();
        $loginTime = date('Y-m-d H:i:s');

        $this->logger->info(
            "Customer Login - ID: {$customerId}, Email: {$email}, Login Time: {$loginTime}"
        );
    }
}
