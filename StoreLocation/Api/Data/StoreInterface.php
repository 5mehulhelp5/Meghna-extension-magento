<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Api\Data;

interface StoreInterface
{

    public const string STORE_ID      = 'store_id';
    public const string NAME          = 'name';
    public const string ADDRESS       = 'address';
    public const string CITY          = 'city';
    public const string PINCODE       = 'pincode';
    public const string PHONE         = 'phone';
    public const string OPENING_HOURS = 'opening_hours';
    public const string IMAGE         = 'image';

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * @param int $storeId
     * @return self
     */
    public function setStoreId(int $storeId): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self;

    /**
     * @return string
     */
    public function getAddress(): string;

    /**
     * @param string $address
     * @return self
     */
    public function setAddress(string $address): self;

    /**
     * @return string
     */
    public function getCity(): string;

    /**
     * @param string $city
     * @return self
     */
    public function setCity(string $city): self;

    /**
     * @return string
     */
    public function getPincode(): string;

    /**
     * @param string $pincode
     * @return self
     */
    public function setPincode(string $pincode): self;

    /**
     * @return string
     */
    public function getPhone(): string;

    /**
     * @param string $phone
     * @return self
     */
    public function setPhone(string $phone): self;

    /**
     * @return string
     */
    public function getOpeningHours(): string;

    /**
     * @param string $openingHours
     * @return self
     */
    public function setOpeningHours(string $openingHours): self;

    /**
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * @param string|null $image
     * @return self
     */
    public function setImage(?string $image): self;

}
