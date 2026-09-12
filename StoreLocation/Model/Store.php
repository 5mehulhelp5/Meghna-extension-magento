<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Model;

use Magento\Framework\Model\AbstractModel;
use Codilar\StoreLocation\Api\Data\StoreInterface;
use Codilar\StoreLocation\Model\ResourceModel\Store as StoreResource;

class Store extends AbstractModel implements StoreInterface
{
    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(StoreResource::class);
    }

    /**
     * @return int|null
     */
    public function getStoreId(): ?int
    {
        $value = $this->getData(self::STORE_ID);
        return $value === null ? null : (int) $value;
    }

    /**
     * @param $storeId
     * @return self
     */
    public function setStoreId($storeId): self
    {
        return $this->setData(self::STORE_ID, $storeId);
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return (string) $this->getData(self::ADDRESS);
    }

    /**
     * @param string $address
     * @return self
     */
    public function setAddress(string $address): self
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return (string) $this->getData(self::CITY);
    }

    /**
     * @param string $city
     * @return self
     */
    public function setCity(string $city): self
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @return string
     */
    public function getPincode(): string
    {
        return (string) $this->getData(self::PINCODE);
    }

    /**
     * @param string $pincode
     * @return self
     */

    public function setPincode(string $pincode): self
    {
        return $this->setData(self::PINCODE, $pincode);
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return (string) $this->getData(self::PHONE);
    }

    /**
     * @param string $phone
     * @return self
     */

    public function setPhone(string $phone): self
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * @return string
     */
    public function getOpeningHours(): string
    {
        return (string) $this->getData(self::OPENING_HOURS);
    }

    /**
     * @param string $openingHours
     * @return self
     */
    public function setOpeningHours(string $openingHours): self
    {
        return $this->setData(self::OPENING_HOURS, $openingHours);
    }

    /**
     * @return string|null
     */
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
