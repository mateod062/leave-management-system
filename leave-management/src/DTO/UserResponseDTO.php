<?php

namespace App\DTO;

class UserResponseDTO
{
    public function __construct(
        private ?int    $id = null,
        private ?string $username = null,
        private ?string $email = null,
        private ?int $leaveBalance = null,
        private ?string $role = null
    ) {}

    public function getLeaveBalance(): ?int
    {
        return $this->leaveBalance;
    }

    public function setLeaveBalance(?int $leaveBalance): void
    {
        $this->leaveBalance = $leaveBalance;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}