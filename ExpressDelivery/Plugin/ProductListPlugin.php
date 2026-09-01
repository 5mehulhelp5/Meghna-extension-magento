<?php
namespace Codilar\ExpressDelivery\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Codilar\ExpressDelivery\Block\Express;
use Magento\Framework\View\LayoutInterface;

class ProductListPlugin
{
    public function __construct(
        protected readonly LayoutInterface $layout
    ) {}


    public function afterGetProductPrice(
        ListProduct $subject,
                    $result,
        \Magento\Catalog\Model\Product $product
    ) {
        // Create the block, assign the template, and pass the product object
        $expressBlock = $this->layout->createBlock(Express::class)
            ->setTemplate('Codilar_ExpressDelivery::express.phtml')
            ->setData('product', $product);

        // Append the rendered template output right after the price
        return $result . $expressBlock->toHtml();
    }
}
