<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Model;

use Magento\Framework\Model\AbstractModel;
use Codilar\StoreLocation\Api\Data\StoreInterface;
use Codilar\StoreLocation\Model\ResourceModel\Store as StoreResource;

class Store extends AbstractModel implements StoreInterface
{
    protected function _construct(): void
    {
        $this->_init(StoreResource::class);
    }

    public function getStoreId(): ?int
    {
        $value = $this->getData(self::STORE_ID);
        return $value === null ? null : (int) $value;
    }

    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    public function setName(string $name): self
    {
        return $this->setData(self::NAME, $name);
    }

    public function getAddress(): string
    {
        return (string) $this->getData(self::ADDRESS);
    }

    public function setAddress(string $address): self
    {
        return $this->setData(self::ADDRESS, $address);
    }

    public function getCity(): string
    {
        return (string) $this->getData(self::CITY);
    }

    public function setCity(string $city): self
    {
        return $this->setData(self::CITY, $city);
    }

    public function getPincode(): string
    {
        return (string) $this->getData(self::PINCODE);
    }

    public function setPincode(string $pincode): self
    {
        return $this->setData(self::PINCODE, $pincode);
    }

    public function getPhone(): string
    {
        return (string) $this->getData(self::PHONE);
    }

    public function setPhone(string $phone): self
    {
        return $this->setData(self::PHONE, $phone);
    }

    public function getOpeningHours(): string
    {
        return (string) $this->getData(self::OPENING_HOURS);
    }

    public function setOpeningHours(string $openingHours): self
    {
        return $this->setData(self::OPENING_HOURS, $openingHours);
    }

    public function getImage(): ?string
    {
        $value = $this->getData(self::IMAGE);
        return $value === null ? null : (string) $value;
    }

    public function setImage(?string $image): self
    {
        return $this->setData(self::IMAGE, $image);
    }
}
