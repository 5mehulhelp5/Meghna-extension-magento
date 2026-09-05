<?php
declare(strict_types=1);

namespace Codilar\ProductEnquiry\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Enquiry extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('product_enquiry', 'entity_id');
    }
}
