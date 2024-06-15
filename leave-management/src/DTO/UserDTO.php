<?php

namespace App\DTO;

class UserDTO
{
    public function __construct(
        private ?int $id = null,
        private ?string $username = null,
        private ?string $email = null,
        private ?string $password = null,
        private ?string $role = null,
        private ?int $teamId = null,
        private ?int $leaveBalance = null
    ) {}

    public function getTeamId(): ?int
    {
        return $this->teamId;
    }

    public function setTeamId(?int $teamId): void
    {
        $this->teamId = $teamId;
    }

    public function getLeaveBalance(): ?int
    {
        return $this->leaveBalance;
    }

    public function setLeaveBalance(?int $leaveBalance): void
    {
        $this->leaveBalance = $leaveBalance;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }
}