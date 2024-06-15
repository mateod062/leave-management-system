<?php

namespace App\Service\User\Interface;

use App\DTO\UserCreationDTO;
use App\DTO\UserDTO;
use App\Entity\UserRole;

interface UserPersistenceServiceInterface
{
    /**
     * Create an employee
     *
     * @param UserCreationDTO $userCreationDTO
     * @return UserDTO
     */
    public function createEmployee(UserCreationDTO $userCreationDTO): UserDTO;

    /**
     * Create a team lead
     *
     * @param UserCreationDTO $userCreationDTO
     * @return UserDTO
     */
    public function createTeamLead(UserCreationDTO $userCreationDTO): UserDTO;

    /**
     * Create a project manager
     *
     * @param UserCreationDTO $userCreationDTO
     * @return UserDTO
     */
    public function createProjectManager(UserCreationDTO $userCreationDTO): UserDTO;

    /**
     * Create an admin
     *
     * @param UserCreationDTO $userCreationDTO
     * @return UserDTO
     */
    public function createAdmin(UserCreationDTO $userCreationDTO): UserDTO;

    /**
     * Create a user
     *
     * @param UserCreationDTO $userCreationDTO
     * @param UserRole $role
     * @return UserDTO
     */
    function createUser(UserCreationDTO $userCreationDTO, UserRole $role): UserDTO;

    /**
     * Update a user
     *
     * @param int $id
     * @param UserDTO $userDTO
     * @return UserDTO
     */
    public function updateUser(int $id, UserDTO $userDTO): UserDTO;

    /**
     * Delete a user
     *
     * @param int $id
     * @return void
     */
    public function deleteUser(int $id): void;
}