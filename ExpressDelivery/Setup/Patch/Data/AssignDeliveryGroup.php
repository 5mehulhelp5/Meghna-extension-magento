<?php

namespace Codilar\ExpressDelivery\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AssignDeliveryGroup implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;
    private $attributeSetCollectionFactory;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup, EavSetupFactory $eavSetupFactory, AttributeSetCollectionFactory $attributeSetCollectionFactory)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
    }

    public static function getDependencies()
    {
        return [AddDeliveryTypeAttribute::class];
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Loop through all attribute sets so the attribute appears everywhere
        $attributeSetCollection = $this->attributeSetCollectionFactory->create();
        foreach ($attributeSetCollection as $attributeSet) {
            $eavSetup->addAttributeToGroup(Product::ENTITY, $attributeSet->getId(), 'Product Details', 'delivery_type');
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function getAliases()
    {
        return [];
    }
}
