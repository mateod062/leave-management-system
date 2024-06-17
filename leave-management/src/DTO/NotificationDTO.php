<?php

namespace App\DTO;

use DateTimeImmutable;

class NotificationDTO
{
    public function __construct(
        private ?string $message = null,
        private ?int $userId = null,
        private DateTimeImmutable $createdAt = new DateTimeImmutable()
    ) {}

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}