<?php

namespace App\Service\Auth;

use App\Service\Auth\Interface\AuthenticationServiceInterface;
use App\Service\DTO\UserCreationDTO;
use App\Service\DTO\UserDTO;
use App\Service\User\UserPersistenceService;
use App\Service\User\UserQueryService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class AuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private readonly UserQueryService       $userFetchService,
        private readonly UserPersistenceService $userPersistenceService,
        private readonly UserPasswordHasher     $passwordHasher,
        private readonly Security               $security
    ) {}

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function registerEmployee(string $username, string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = new UserCreationDTO($username, $email, $password);

        return $this->userPersistenceService->createEmployee($user);
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function registerTeamLead(string $username, string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = new UserCreationDTO($username, $email, $password);

        return $this->userPersistenceService->createTeamLead($user);
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function registerProjectManager(string $username, string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = new UserCreationDTO($username, $email, $password);

        return $this->userPersistenceService->createProjectManager($user);
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function registerAdmin(string $username, string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = new UserCreationDTO($username, $email, $password);

        return $this->userPersistenceService->createAdmin($user);
    }

    /**
     * @throws ReflectionException
     */
    public function login(string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = $this->userFetchService->getUserByEmail($email);

        if ($this->passwordHasher->isPasswordValid($this->security->getUser(), $password) === false) {
            throw new AuthenticationException('Invalid credentials');
        }

        return $user;
    }

    /**
     * @throws ReflectionException
     */
    public function getAuthenticatedUser(): UserDTO
    {
        return $this->userFetchService->getUserByEmail($this->security->getUser()->getUserIdentifier());
    }
}