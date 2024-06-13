<?php

namespace App\Service\User;

use App\Entity\User;
use App\Entity\UserRole;
use App\Event\UserCreatedEvent;
use App\Repository\UserRepository;
use App\Service\DTO\UserCreationDTO;
use App\Service\DTO\UserDTO;
use App\Service\Mapper\MapperService;
use App\Service\User\Interface\UserPersistenceServiceInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserPersistenceService implements UserPersistenceServiceInterface
{
    private const ENTITY_NAME = 'User';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MapperService $mapperService,
        private readonly EventDispatcherInterface $eventDispatcher
    ){}

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function createEmployee(UserCreationDTO $userCreationDTO): UserDTO
    {
        return $this->createUser($userCreationDTO, UserRole::ROLE_EMPLOYEE);
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function createTeamLead(UserCreationDTO $userCreationDTO): UserDTO
    {
        return $this->createUser($userCreationDTO, UserRole::ROLE_TEAM_LEAD);
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function createProjectManager(UserCreationDTO $userCreationDTO): UserDTO
    {
        return $this->createUser($userCreationDTO, UserRole::ROLE_PROJECT_MANAGER);
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function createAdmin(UserCreationDTO $userCreationDTO): UserDTO
    {
        return $this->createUser($userCreationDTO, UserRole::ROLE_ADMIN);
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function createUser(UserCreationDTO $userCreationDTO, UserRole $role): UserDTO
    {
        $user = $this->mapperService->mapToEntity($userCreationDTO, User::class);
        $user->setRole($role);

        $this->eventDispatcher->dispatch(new UserCreatedEvent($user), UserCreatedEvent::NAME);
        $userDTO = $this->mapperService->mapToDTO($this->userRepository->save($user));

        return $this->mapperService->mapToDTO($this->userRepository->save($user));
    }

    /**
     * @throws ReflectionException
     */
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

        return $this->mapperService->mapToDTO($user);
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