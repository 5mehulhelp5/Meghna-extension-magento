<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StoreWallet extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('codilar_store_wallet_ledger', 'entity_id');
    }
}
