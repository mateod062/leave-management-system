<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\UserRepository;
use App\Service\DTO\UserCreationDTO;
use App\Service\DTO\UserDTO;
use Doctrine\ORM\EntityNotFoundException;

class UserPersistenceService
{
    private const ENTITY_NAME = 'User';

    public function __construct(private readonly UserRepository $userRepository){}

    public function createEmployee(UserCreationDTO $userCreationDTO): UserDTO
    {
        return $this->createUser($userCreationDTO, UserRole::ROLE_EMPLOYEE);
    }

    public function createTeamLead(UserCreationDTO $userCreationDTO): UserDTO
    {
        return $this->createUser($userCreationDTO, UserRole::ROLE_TEAM_LEAD);
    }

    public function createProjectManager(UserCreationDTO $userCreationDTO): UserDTO
    {
        return $this->createUser($userCreationDTO, UserRole::ROLE_PROJECT_MANAGER);
    }

    public function createAdmin(UserCreationDTO $userCreationDTO): UserDTO
    {
        return $this->createUser($userCreationDTO, UserRole::ROLE_ADMIN);
    }

    private function createUser(UserCreationDTO $userCreationDTO, UserRole $role): UserDTO
    {
        $user = new User();
        $user->setUsername($userCreationDTO->getUsername());
        $user->setEmail($userCreationDTO->getEmail());
        $user->setPassword($userCreationDTO->getPassword());
        $user->setRole($role);

        $this->userRepository->save($user);

        return UserDTO::fromEntity($user);
    }

    public function updateUser(int $id, UserDTO $userDTO): UserDTO
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $id));
        }

        $user->setUsername($userDTO->getUsername());
        $user->setEmail($userDTO->getEmail());
        $user->setPassword($userDTO->getPassword());
        $user->setRole(UserRole::tryFrom($userDTO->getRole()));

        $this->userRepository->save($user);

        return UserDTO::fromEntity($user);
    }

    public function deleteUser(int $id): void
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $id));
        }

        $this->userRepository->delete($user);
    }
}