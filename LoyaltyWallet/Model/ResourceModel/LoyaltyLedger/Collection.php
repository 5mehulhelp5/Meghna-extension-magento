<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model\ResourceModel\LoyaltyLedger;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(
            \Codilar\LoyaltyWallet\Model\LoyaltyLedger::class,
            \Codilar\LoyaltyWallet\Model\ResourceModel\LoyaltyLedger::class
        );
    }
}
