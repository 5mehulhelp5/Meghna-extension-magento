<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Model\ResourceModel\Store;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'store_id';

    protected function _construct(): void
    {
        $this->_init(
            \Codilar\StoreLocation\Model\Store::class,
            \Codilar\StoreLocation\Model\ResourceModel\Store::class
        );
    }
}
