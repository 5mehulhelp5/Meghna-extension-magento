<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model;

use Magento\Framework\Model\AbstractModel;
use Codilar\LoyaltyWallet\Api\Data\LoyaltyLedgerInterface;

class LoyaltyLedger extends AbstractModel implements LoyaltyLedgerInterface
{
    protected function _construct()
    {
        $this->_init(\Codilar\LoyaltyWallet\Model\ResourceModel\LoyaltyLedger::class);
    }

    public function getEntityId(): ?int
    {
        return $this->getData('entity_id') === null ? null : (int)$this->getData('entity_id');
    }

    public function setEntityId($entityId): LoyaltyLedgerInterface
    {
        return $this->setData('entity_id', $entityId);
    }

    public function getCustomerId(): int
    {
        return (int)$this->getData('customer_id');
    }

    public function setCustomerId(int $customerId): LoyaltyLedgerInterface
    {
        return $this->setData('customer_id', $customerId);
    }

    public function getOrderIncrementId(): ?string
    {
        return $this->getData('order_increment_id');
    }

    public function setOrderIncrementId(?string $incrementId): LoyaltyLedgerInterface
    {
        return $this->setData('order_increment_id', $incrementId);
    }

    public function getQuantity(): int
    {
        return (int)$this->getData('quantity');
    }

    public function setQuantity(int $quantity): LoyaltyLedgerInterface
    {
        return $this->setData('quantity', $quantity);
    }

    public function getBalanceAfter(): int
    {
        return (int)$this->getData('balance_after');
    }

    public function setBalanceAfter(int $balance): LoyaltyLedgerInterface
    {
        return $this->setData('balance_after', $balance);
    }


}
