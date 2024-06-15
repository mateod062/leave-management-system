<?php

namespace App\Service\User\Interface;

use App\DTO\UserDTO;

interface UserQueryServiceInterface
{
    /**
     * Get all users
     *
     * @return array
     */
    public function getUsers(): array;

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