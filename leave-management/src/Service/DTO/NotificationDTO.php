<?php

namespace App\Service\DTO;

use App\Entity\Notification;

class NotificationDTO
{
    public function __construct(private string $message, private int $userId) {}

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}