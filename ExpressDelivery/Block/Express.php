<?php
declare(strict_types=1);

namespace Codilar\ExpressDelivery\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Exception;

class Express extends Template
{
    public function __construct(
        Template\Context $context,
        protected ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get the human-readable text for the product's delivery type attribute.
     */
    public function getDeliveryLabel(mixed $product): string
    {
        if (!$product) {
            return '';
        }

        // Fallback: If the attribute isn't loaded in the collection, fetch it via repository
        $deliveryTypeId = $product->getData('delivery_type');
        if (!$deliveryTypeId && $product->getId()) {
            try {
                $product = $this->productRepository->getById((int) $product->getId());
                $deliveryTypeId = $product->getData('delivery_type');
            } catch (Exception) {
                return '';
            }
        }

        if (!$deliveryTypeId) {
            return '';
        }

        try {
            $optionText = $product->getResource()
                ?->getAttribute('delivery_type')
                ?->getSource()
                ?->getOptionText($deliveryTypeId);

            return is_string($optionText) ? $optionText : '';
        } catch (Exception) {
            return '';
        }
    }
}
