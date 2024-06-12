<?php

namespace App\Service\User\Interface;

use App\Service\DTO\UserDTO;

interface UserQueryServiceInterface
{
    /**
     * Get all users
     *
     * @return array
     */
    public function getUsers(): array;

    /**
     * Get all team members
     *
     * @param int $teamId
     *
     * @return array
     */
    public function getTeamMembers(int $teamId): array;

    /**
     * Get user by id
     *
     * @param int $id
     *
     * @return UserDTO
     */
    public function getUserById(int $id): UserDTO;

    /**
     * Get user by email
     *
     * @param string $email
     *
     * @return UserDTO
     */
    public function getUserByEmail(string $email): UserDTO;

}