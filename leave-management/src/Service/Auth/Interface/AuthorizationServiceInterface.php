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

    /**
     * Deny access if the user is not a member of the team
     *
     * @param int $teamId
     * @return void
     */
    public function denyAccessUnlessMemberOfTeam(int $teamId): void;

    /**
     * Deny access if the user is not the poster of the comment
     *
     * @param int $commentId
     * @return void
     */
    public function denyUnlessCommentPoster(int $commentId): void;
}