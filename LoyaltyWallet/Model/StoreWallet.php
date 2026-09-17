<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model;

use Magento\Framework\Model\AbstractModel;
use Codilar\LoyaltyWallet\Api\Data\StoreWalletInterface;

class StoreWallet extends AbstractModel implements StoreWalletInterface
{
    protected function _construct()
    {
        $this->_init(\Codilar\LoyaltyWallet\Model\ResourceModel\StoreWallet::class);
    }

    public function getEntityId()
    {
        return $this->getData('entity_id');
    }

    public function setEntityId($entityId)
    {
        return $this->setData('entity_id', $entityId);
    }

    public function getCustomerId(): int
    {
        return (int)$this->getData('customer_id');
    }

    public function setCustomerId(int $customerId)
    {
        return $this->setData('customer_id', $customerId);
    }

    public function getOrderIncrementId(): ?string
    {
        return $this->getData('order_increment_id');
    }

    public function setOrderIncrementId(?string $orderIncrementId)
    {
        return $this->setData('order_increment_id', $orderIncrementId);
    }

    public function getAmount(): float
    {
        return (float)$this->getData('amount');
    }

    public function setAmount(float $amount)
    {
        return $this->setData('amount', $amount);
    }

    public function getBalanceAfter(): float
    {
        return (float)$this->getData('balance_after');
    }

    public function setBalanceAfter(float $balanceAfter)
    {
        return $this->setData('balance_after', $balanceAfter);
    }

    public function getComment(): ?string
    {
        return $this->getData('comment');
    }

    public function setComment(?string $comment)
    {
        return $this->setData('comment', $comment);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getData('created_at');
    }
}
