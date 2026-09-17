<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model\Total;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ShippingAssignment;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Psr\Log\LoggerInterface;

class Wallet extends AbstractTotal
{
    protected $_code = 'wallet';

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
        $this->setCode('wallet');
    }

    /**
     * Collect wallet total.
     */
    public function collect(
        Quote $quote,
        ShippingAssignment|\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): self {
        parent::collect($quote, $shippingAssignment, $total);

        $appliedAmount = (float)$quote->getData('applied_wallet_amount');

        $this->logger->info(
            'WALLET COLLECT - Quote ID: ' . $quote->getId()
            . ' | Applied Wallet: ' . $appliedAmount
            . ' | Subtotal: ' . $total->getSubtotal()
            . ' | Shipping: ' . $total->getShippingAmount()
            . ' | Tax: ' . $total->getTaxAmount()
            . ' | Grand Total: ' . $total->getGrandTotal()
        );

        if ($appliedAmount <= 0) {
            return $this;
        }

        $grandTotal = (float)$total->getGrandTotal();

        /*
         * If Magento has not calculated the grand total yet,
         * do not apply wallet at this stage.
         */
        if ($grandTotal <= 0) {
            $this->logger->info(
                'WALLET COLLECT SKIPPED - Grand total is zero.'
            );

            return $this;
        }

        $minimumPayableAmount = 1.00;

        $maximumWalletDiscount = $grandTotal - $minimumPayableAmount;

        if ($maximumWalletDiscount <= 0) {
            $this->logger->info(
                'WALLET COLLECT SKIPPED - Order total is too low to apply wallet.'
            );

            return $this;
        }

        $walletDiscount = min(
            $appliedAmount,
            $maximumWalletDiscount
        );
        /*
         * Add wallet as a negative total.
         */
        $total->setTotalAmount(
            $this->getCode(),
            -$walletDiscount
        );

        $total->setBaseTotalAmount(
            $this->getCode(),
            -$walletDiscount
        );

        /*
         * Update grand total.
         */
        $total->setGrandTotal(
            $grandTotal - $walletDiscount
        );

        $baseGrandTotal = (float)$total->getBaseGrandTotal();

        $total->setBaseGrandTotal(
            $baseGrandTotal - $walletDiscount
        );

        $this->logger->info(
            'WALLET COLLECT APPLIED - Quote ID: ' . $quote->getId()
            . ' | Wallet: ' . $walletDiscount
            . ' | New Grand Total: ' . $total->getGrandTotal()
        );

        return $this;
    }

    /**
     * Display wallet in checkout totals.
     */
    public function fetch(
        Quote $quote,
        Total $total
    ): ?array {
        $appliedAmount = (float)$quote->getData('applied_wallet_amount');

        if ($appliedAmount <= 0) {
            return null;
        }

        return [
            'code' => $this->getCode(),
            'title' => __('Store Wallet'),
            'value' => -$appliedAmount
        ];
    }
}
