<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LoyaltyLedger extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('Codilar_loyalty_ledger', 'entity_id');
    }
}
