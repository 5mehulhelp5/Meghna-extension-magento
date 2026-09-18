<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Api\Data;

/**
 * Interface StoreWalletInterface
 * Represents a store wallet ledger transaction entry.
 */
interface StoreWalletInterface
{
    /**
     * Get Entity ID
     *
     * @return int|string|null
     */
    public function getEntityId();

    /**
     * Set Entity ID
     *
     * @param int|string $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get Customer ID
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set Customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId);

    /**
     * Get Order Increment ID associated with the transaction
     *
     * @return string|null
     */
    public function getOrderIncrementId(): ?string;

    /**
     * Set Order Increment ID
     *
     * @param string|null $orderIncrementId
     * @return $this
     */
    public function setOrderIncrementId(?string $orderIncrementId);

    /**
     * Get Transaction Amount (positive for credit/add, negative for debit/spend)
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Set Transaction Amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount(float $amount);

    /**
     * Get Wallet Balance After this transaction
     *
     * @return float
     */
    public function getBalanceAfter(): float;

    /**
     * Set Wallet Balance After this transaction
     *
     * @param float $balanceAfter
     * @return $this
     */
    public function setBalanceAfter(float $balanceAfter);

    /**
     * Get Transaction Comment / Reason (e.g., Conversion, Order Refund, etc.)
     *
     * @return string|null
     */
    public function getComment(): ?string;

    /**
     * Set Transaction Comment / Reason
     *
     * @param string|null $comment
     * @return $this
     */
    public function setComment(?string $comment);

    /**
     * Get Creation Timestamp
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;
}
