<?php
namespace Codilar\StoreLocation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Codilar\StoreLocation\Model\ResourceModel\Store\CollectionFactory;

class StoreList extends Template
{
    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return \Codilar\StoreLocation\Model\ResourceModel\Store\Collection
     */
    public function getStores()
    {
        return $this->collectionFactory->create();
    }

}
