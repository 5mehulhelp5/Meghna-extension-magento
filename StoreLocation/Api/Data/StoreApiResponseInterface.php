<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Api\Data;

interface StoreApiResponseInterface
{
    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self;

    /**
     * @return int
     */
    public function getCode(): int;

    /**
     * @param int $code
     * @return $this
     */
    public function setCode(int $code): self;

    /**
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self;

    /**
     * @return \Codilar\StoreLocation\Api\Data\StoreInterface[]
     */
    public function getData(): array;

    /**
     * @param \Codilar\StoreLocation\Api\Data\StoreInterface[] $data
     * @return $this
     */
    public function setData(array $data): self;
}
