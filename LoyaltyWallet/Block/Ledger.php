<?php
namespace Codilar\LoyaltyWallet\Block;

use Magento\Framework\View\Element\Template;

class Ledger extends Template
{

    /**
     * @param string $createdAt
     * @return string
     * @throws \DateMalformedStringException
     */
    public function getExpiryDate(string $createdAt): string
    {
        $date = new \DateTime($createdAt);
        $date->modify('+12 months');
        return $date->format('M d, Y');
    }
}
