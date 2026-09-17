<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Api\Data;

interface StoreWalletInterface
{
    public function getEntityId();
    public function setEntityId($entityId);
    public function getCustomerId(): int;
    public function setCustomerId(int $customerId);
    public function getOrderIncrementId(): ?string;
    public function setOrderIncrementId(?string $orderIncrementId);
    public function getAmount(): float;
    public function setAmount(float $amount);
    public function getBalanceAfter(): float;
    public function setBalanceAfter(float $balanceAfter);
    public function getComment(): ?string;
    public function setComment(?string $comment);
    public function getCreatedAt(): ?string;
}
