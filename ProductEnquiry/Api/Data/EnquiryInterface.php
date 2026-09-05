<?php

declare(strict_types=1);

namespace Codilar\ProductEnquiry\Api\Data;

interface EnquiryInterface
{
    public const ENTITY_ID = 'entity_id';
    public const NAME = 'name';
    public const ADDRESS = 'address';
    public const EMAIL = 'email';
    public const SKU = 'sku';
    public const QTY = 'qty';
    public const CREATED_AT = 'created_at';

    public function getEntityId(): ?int;

    public function getName(): string;

    public function setName(string $name): self;

    public function getAddress(): string;

    public function setAddress(string $address): self;

    public function getEmail(): string;

    public function setEmail(string $email): self;

    public function getSku(): string;

    public function setSku(string $sku): self;

    public function getQty(): int;

    public function setQty(int $qty): self;

    public function getCreatedAt(): ?string;
}
