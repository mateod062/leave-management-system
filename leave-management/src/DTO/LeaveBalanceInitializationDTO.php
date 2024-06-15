<?php

namespace App\DTO;

class LeaveBalanceInitializationDTO
{
    public function __construct(
        private ?int $userId = null,
        private ?int $balance = null,
    ) {}

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getBalance(): ?int
    {
        return $this->balance;
    }

    public function setBalance(?int $balance): void
    {
        $this->balance = $balance;
    }
}