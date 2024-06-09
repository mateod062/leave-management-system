<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DTO\UserDTO;
use App\Service\Mapper\MapperService;
use Doctrine\ORM\EntityNotFoundException;
use ReflectionException;

class UserQueryService
{
    private const ENTITY_NAME = 'User';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MapperService $mapperService
    ){}

    /**
     * @throws ReflectionException
     */
    public function getUsers(): array
    {
        $users = $this->userRepository->findAll();

        return array_map(fn(User $user) => $this->mapperService->mapToDTO($user), $users);
    }

    /**
     * @throws ReflectionException
     */
    public function getUserById(int $id): UserDTO
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $id));
        }

        return $this->mapperService->mapToDTO($user);
    }

    /**
     * @throws ReflectionException
     */
    public function getUserByEmail(string $email): UserDTO
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with email %s not found', self::ENTITY_NAME, $email));
        }

        return $this->mapperService->mapToDTO($user);
    }

    /**
     * @throws ReflectionException
     */
    public function getUserByUsername(string $username): UserDTO
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with username %s not found', self::ENTITY_NAME, $username));
        }

        return $this->mapperService->mapToDTO($user);
    }
}