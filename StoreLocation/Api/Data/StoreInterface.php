<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Api\Data;

interface StoreInterface
{
    public const STORE_ID      = 'store_id';
    public const NAME          = 'name';
    public const ADDRESS       = 'address';
    public const CITY          = 'city';
    public const PINCODE       = 'pincode';
    public const PHONE         = 'phone';
    public const OPENING_HOURS = 'opening_hours';
    public const IMAGE         = 'image';

    public function getStoreId(): ?int;
    public function getName(): string;
    public function setName(string $name): self;
    public function getAddress(): string;
    public function setAddress(string $address): self;
    public function getCity(): string;
    public function setCity(string $city): self;
    public function getPincode(): string;
    public function setPincode(string $pincode): self;
    public function getPhone(): string;
    public function setPhone(string $phone): self;
    public function getOpeningHours(): string;
    public function setOpeningHours(string $openingHours): self;
    public function getImage(): ?string;
    public function setImage(?string $image): self;
}
