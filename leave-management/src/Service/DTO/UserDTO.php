<?php

namespace App\Service\DTO;

use App\Entity\User;
use App\Entity\UserRole;

class UserDTO
{
    public function __construct(
        private ?int $id,
        private string $username,
        private string $email,
        private string $password,
        private string $role
    ) {}

    public static function fromEntity(User $user): UserDTO
    {
        return new UserDTO(
            $user->getId(),
            $user->getUsername(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getRole()
        );
    }

    public static function toDTO(UserDTO $userDTO): User
    {
        $user = new User();
        $user->setId($userDTO->getId());
        $user->setUsername($userDTO->getUsername());
        $user->setEmail($userDTO->getEmail());
        $user->setPassword($userDTO->getPassword());
        $user->setRole(UserRole::tryFrom($userDTO->getRole()));

        return $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }
}