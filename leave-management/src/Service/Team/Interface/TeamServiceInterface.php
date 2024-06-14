<?php

namespace App\Service\Team\Interface;

interface TeamServiceInterface
{
    /**
     * Get all team members
     *
     * @param int $teamId
     *
     * @return array
     */
    public function getTeamMembers(int $teamId): array;
}