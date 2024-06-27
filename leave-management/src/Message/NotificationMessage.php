<?php

namespace App\Message;

class NotificationMessage
{
    public function __construct(
        private readonly int $userId,
        private readonly string $message
    )
    {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}