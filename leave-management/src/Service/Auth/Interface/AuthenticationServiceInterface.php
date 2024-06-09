<?php

namespace App\Service\Auth\Interface;

use App\Service\DTO\UserDTO;

interface AuthenticationServiceInterface
{
    /**
     * Register a new employee
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return UserDTO
     */
    public function registerEmployee(string $username, string $email, string $password): UserDTO;

    /**
     * Register a new team lead
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return UserDTO
     */
    public function registerTeamLead(string $username, string $email, string $password): UserDTO;

    /**
     * Register a new project manager
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return UserDTO
     */
    public function registerProjectManager(string $username, string $email, string $password): UserDTO;

    /**
     * Register a new admin
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return UserDTO
     */
    public function registerAdmin(string $username, string $email, string $password): UserDTO;

    /**
     * Login a user
     *
     * @param string $email
     * @param string $password
     * @return UserDTO
     */
    public function login(string $email, string $password): UserDTO;

    /**
     * Get the authenticated user
     *
     * @return UserDTO
     */
    public function getAuthenticatedUser(): UserDTO;
}