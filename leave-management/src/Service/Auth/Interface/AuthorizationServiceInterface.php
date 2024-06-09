<?php

namespace App\Service\Auth\Interface;

interface AuthorizationServiceInterface
{
    /**
     * Check if the user has the given role
     *
     * @param string $role
     * @return bool
     */
    public function isGranted(string $role): bool;

    /**
     * Deny access if the user does not have the given role
     *
     * @param string $role
     * @return void
     */
    public function denyAccessUnlessGranted(string $role): void;
}