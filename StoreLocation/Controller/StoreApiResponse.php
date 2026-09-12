<?php
declare(strict_types=1);

namespace Codilar\StoreLocation\Model;

use Codilar\StoreLocation\Api\Data\StoreApiResponseInterface;

class StoreApiResponse implements StoreApiResponseInterface
{
    private string $status;
    private int $code;
    private string $message;
    private $data; // Removed "mixed" type hint

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

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }
}
