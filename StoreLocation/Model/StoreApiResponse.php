<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Model;

use Codilar\StoreLocation\Api\Data\StoreApiResponseInterface;

class StoreApiResponse implements StoreApiResponseInterface
{
    private string $status = '';
    private int $code = 200;
    private ?string $message = null;
    private array $data = [];

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }
}
