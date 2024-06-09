<?php

namespace App\Service;

use App\Service\DTO\UserCreationDTO;
use App\Service\DTO\UserDTO;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class AuthenticationService
{
    public function __construct(
        private readonly UserFetchService       $userFetchService,
        private readonly UserPersistenceService $userPersistenceService,
        private readonly UserPasswordHasher     $passwordHasher,
        private readonly Security               $security
    ) {}

    public function registerEmployee(string $username, string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = new UserCreationDTO($username, $email, $password);

        return $this->userPersistenceService->createEmployee($user);
    }

    public function registerTeamLead(string $username, string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = new UserCreationDTO($username, $email, $password);

        return $this->userPersistenceService->createTeamLead($user);
    }

    public function registerProjectManager(string $username, string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = new UserCreationDTO($username, $email, $password);

        return $this->userPersistenceService->createProjectManager($user);
    }

    public function registerAdmin(string $username, string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = new UserCreationDTO($username, $email, $password);

        return $this->userPersistenceService->createAdmin($user);
    }

    public function login(string $email, string $password): UserDTO
    {
        $password = $this->passwordHasher->hashPassword($this->security->getUser(), $password);
        $user = $this->userFetchService->getUserByEmail($email);

        if ($this->passwordHasher->isPasswordValid($this->security->getUser(), $password) === false) {
            throw new AuthenticationException('Invalid credentials');
        }

        return $user;
    }

    public function getAuthenticatedUser(): UserDTO
    {
        return $this->userFetchService->getUserByEmail($this->security->getUser()->getUserIdentifier());
    }
}