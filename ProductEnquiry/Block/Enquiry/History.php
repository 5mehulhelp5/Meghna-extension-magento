<?php
declare(strict_types=1);

namespace Codilar\ProductEnquiry\Block\Enquiry;

use Codilar\ProductEnquiry\Model\ResourceModel\Enquiry\Collection;
use Codilar\ProductEnquiry\Model\ResourceModel\Enquiry\CollectionFactory;
use Magento\Framework\View\Element\Template;

class History extends Template
{
    /**
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        private readonly CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getEnquiriesCollection(): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->setOrder('entity_id', 'DESC');

        return $collection;
    }
}
