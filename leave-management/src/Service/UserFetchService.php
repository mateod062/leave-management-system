<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DTO\UserDTO;
use Doctrine\ORM\EntityNotFoundException;

class UserFetchService
{
    private const ENTITY_NAME = 'User';

    public function __construct(private readonly UserRepository $userRepository){}

    public function getUsers(): array
    {
        $users = $this->userRepository->findAll();

        return array_map(fn(User $user) => UserDTO::fromEntity($user), $users);
    }

    public function getUserById(int $id): UserDTO
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $id));
        }

        return UserDTO::fromEntity($user);
    }

    public function getUserByEmail(string $email): UserDTO
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with email %s not found', self::ENTITY_NAME, $email));
        }

        return UserDTO::fromEntity($user);
    }

    public function getUserByUsername(string $username): UserDTO
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with username %s not found', self::ENTITY_NAME, $username));
        }

        return UserDTO::fromEntity($user);
    }
}