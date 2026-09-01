<?php
namespace Codilar\ProductSlider\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Psr\Log\LoggerInterface;

class CategorySlider extends Template implements BlockInterface
{
    protected $_template = 'widget/category_slider.phtml';

    public function __construct(
        Template\Context $context,
        private readonly CatalogHelper $catalogHelper,
        private readonly CollectionFactory $productCollectionFactory,
        private readonly CategoryFactory $categoryFactory,
        private readonly ImageHelper $imageHelper,
        private readonly LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Modern replacement for deprecated Registry to safely get current product
     */
    public function getCurrentProduct()
    {
        return $this->catalogHelper->getProduct();
    }

    /**
     * Get products belonging to the same category as current product
     */
    public function getCategoryProducts()
    {
        try {
            $product = $this->getCurrentProduct();

            if (!$product || !$product->getId()) {
                return [];
            }

            $categoryIds = $product->getCategoryIds();
            if (empty($categoryIds)) {
                return [];
            }

            $categoryId = reset($categoryIds);
            $category = $this->categoryFactory->create()->load($categoryId);

            if (!$category->getId()) {
                return [];
            }

            $pageSize = (int) $this->getData('product_count');
            if ($pageSize <= 0) {
                $pageSize = 10;
            }

            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('*')
                ->addCategoryFilter($category)
                ->addFieldToFilter('entity_id', ['neq' => $product->getId()])
                ->setPageSize($pageSize);

            return $collection;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
    }

    /**
     * Cleaned up image helper without using ObjectManager
     */
    public function getImage($product, $imageId)
    {
        return $this->imageHelper->init($product, $imageId);
    }
}
