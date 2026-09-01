<?php
namespace Codilar\ExpressDelivery\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;

class AssignDeliveryGroup implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;
    private $attributeSetCollectionFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeSetCollectionFactory $attributeSetCollectionFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Loop through all attribute sets so the attribute appears everywhere
        $attributeSetCollection = $this->attributeSetCollectionFactory->create();
        foreach ($attributeSetCollection as $attributeSet) {
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSet->getId(),
                'Product Details',
                'delivery_type'
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [AddDeliveryTypeAttribute::class];
    }

    public function getAliases()
    {
        return [];
    }
}
