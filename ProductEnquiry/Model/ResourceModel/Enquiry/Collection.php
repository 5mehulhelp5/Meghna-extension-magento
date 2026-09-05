<?php
declare(strict_types=1);

namespace Codilar\ProductEnquiry\Model\ResourceModel\Enquiry;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Codilar\ProductEnquiry\Model\Enquiry as Model;
use Codilar\ProductEnquiry\Model\ResourceModel\Enquiry as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

}
