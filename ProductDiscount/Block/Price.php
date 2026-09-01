<?php

namespace Codilar\ProductDiscount\Block;

use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

class Price extends Template
{
    public function getDiscountHtml(Product $product): string
    {
        try {
            $regularPrice = (float) $product
                ->getPriceInfo()
                ->getPrice('regular_price')
                ->getAmount()
                ->getValue();

            $finalPrice = (float) $product
                ->getPriceInfo()
                ->getPrice('final_price')
                ->getAmount()
                ->getValue();

            if ($regularPrice <= 0 || $finalPrice >= $regularPrice) {
                return '';
            }

            $discountPercent = round(
                (($regularPrice - $finalPrice) / $regularPrice) * 100
            );

            $regularPriceHtml = '$' . number_format($regularPrice, 2);
            $finalPriceHtml = '$' . number_format($finalPrice, 2);

            return $this->setTemplate(
                'Codilar_ProductDiscount::product/discount.phtml'
            )
                ->setRegularPrice($regularPriceHtml)
                ->setFinalPrice($finalPriceHtml)
                ->setDiscountPercent($discountPercent)
                ->toHtml();

        } catch (\Throwable $e) {
            return '';
        }
    }
}
