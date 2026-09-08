<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Store extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('store_location', 'store_id');
    }
}
