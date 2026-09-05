<?php
declare(strict_types=1);

namespace Codilar\ProductEnquiry\Model;

use Codilar\ProductEnquiry\Api\Data\EnquiryInterface;
use Codilar\ProductEnquiry\Model\ResourceModel\Enquiry as EnquiryResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class Enquiry extends AbstractModel implements EnquiryInterface
{
    /**
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(EnquiryResource::class);
    }

    public function getEntityId(): ?int
    {
        $value = $this->getData(self::ENTITY_ID);
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

    public function getEmail(): string
    {
        return (string) $this->getData(self::EMAIL);
    }

    public function setEmail(string $email): self
    {
        return $this->setData(self::EMAIL, $email);
    }

    public function getSku(): string
    {
        return (string) $this->getData(self::SKU);
    }

    public function setSku(string $sku): self
    {
        return $this->setData(self::SKU, $sku);
    }

    public function getQty(): int
    {
        return (int) $this->getData(self::QTY);
    }

    public function setQty(int $qty): self
    {
        return $this->setData(self::QTY, $qty);
    }

    public function getCreatedAt(): ?string
    {
        $value = $this->getData(self::CREATED_AT);
        return $value === null ? null : (string) $value;
    }
}
