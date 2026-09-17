<?php

declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TransferWalletAmountToOrder implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        $walletAmount = (float) $quote->getData('applied_wallet_amount');

        $order->setData('applied_wallet_amount', $walletAmount);
    }
}
