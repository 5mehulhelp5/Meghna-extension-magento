<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Api\Data;

interface LoyaltyLedgerInterface
{
    /**
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * @param int $entityId
     * @return LoyaltyLedgerInterface
     */
    public function setEntityId(int $entityId): LoyaltyLedgerInterface;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     * @return LoyaltyLedgerInterface
     */
    public function setCustomerId(int $customerId): LoyaltyLedgerInterface;

    /**
     * @return string|null
     */
    public function getOrderIncrementId(): ?string;

    /**
     * @param string|null $incrementId
     * @return LoyaltyLedgerInterface
     */
    public function setOrderIncrementId(?string $incrementId): LoyaltyLedgerInterface;

    /**
     * @return int
     */
    public function getQuantity(): int;

    /**
     * @param int $quantity
     * @return LoyaltyLedgerInterface
     */
    public function setQuantity(int $quantity): LoyaltyLedgerInterface;

    /**
     * @return int
     */
    public function getBalanceAfter(): int;

    /**
     * @param int $balance
     * @return LoyaltyLedgerInterface
     */
    public function setBalanceAfter(int $balance): LoyaltyLedgerInterface;
}
