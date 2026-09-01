<?php

namespace Codilar\ProductDiscount\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;

class ProductPricePlugin
{
    public function afterGetProductPrice(
        ListProduct $subject,
                    $result,
        Product $product
    ) {
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

        if ($finalPrice < $regularPrice) {
            $discountPercent = (($regularPrice - $finalPrice) / $regularPrice) * 100;
            // Additional price manipulation logic can hook here if needed
        }

        return $result;
    }
}
