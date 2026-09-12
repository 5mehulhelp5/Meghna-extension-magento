<?php
namespace Codilar\StoreLocation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Codilar\StoreLocation\Model\ResourceModel\Store\CollectionFactory;

class StoreList extends Template
{
    protected $collectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    public function getStores()
    {
        return $this->collectionFactory->create();
    }
}
