<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class CopyWalletAmountToOrder implements ObserverInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        if (!$order || !$quote) {
            return;
        }

        $walletAmount = (float)$quote->getData(
            'applied_wallet_amount'
        );

        $order->setData(
            'applied_wallet_amount',
            $walletAmount
        );

        $this->logger->info(
            'Wallet amount copied from quote to order. '
            . 'Quote ID: ' . $quote->getId()
            . ' | Order ID: ' . $order->getIncrementId()
            . ' | Wallet Amount: ₹' . $walletAmount
        );
    }
}
