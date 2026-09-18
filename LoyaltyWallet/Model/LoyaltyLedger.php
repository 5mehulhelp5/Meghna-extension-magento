<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Model;

use Codilar\LoyaltyWallet\Api\Data\LoyaltyLedgerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

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
    public function getComment(): ?string
    {
        return $this->getData('comment');
    }

    public function setComment(?string $comment): LoyaltyLedgerInterface
    {
        return $this->setData('comment', $comment);
    }
    /**
     * Enforce immutability: Block updates on existing ledger records.
     */
    public function beforeSave()
    {
        parent::beforeSave();

        // If the record already exists in the database ($this->getId()), block the update!
        if ($this->getId() && !$this->isObjectNew()) {
            throw new LocalizedException(
                __('Ledger entries are strictly immutable. You cannot modify past transactions.')
            );
        }
    }

    /**
     * Enforce immutability: Block deletions of ledger records.
     */
    public function beforeDelete()
    {
        throw new LocalizedException(
            __('Ledger entries cannot be deleted. Use compensating entries for corrections.')
        );
    }

}
