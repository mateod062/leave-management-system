<?php

namespace App\Service\Team\Interface;

use App\DTO\TeamCreationDTO;
use App\DTO\TeamResponseDTO;
use App\DTO\UserResponseDTO;

interface TeamServiceInterface
{
    /**
     * Get the leading team of a team lead
     *
     * @param int $teamLeadId
     *
     * @return TeamResponseDTO
     */
    public function getLeadingTeam(int $teamLeadId): TeamResponseDTO;

    /**
     * Get all teams managed by a project manager
     *
     * @param int $projectManagerId
     *
     * @return array
     */
    public function getManagedTeams(int $projectManagerId): array;

    /**
     * Get all team members
     *
     * @param int $teamId
     *
     * @return array
     */
    public function getTeamMembers(int $teamId): array;

    /**
     * Get the project manager of a team
     *
     * @param int $teamId
     *
     * @return UserResponseDTO
     */
    public function getProjectManager(int $teamId): UserResponseDTO;

    /**
     * Get the team lead of a team
     *
     * @param int $teamId
     *
     * @return UserResponseDTO
     */
    public function getTeamLead(int $teamId): UserResponseDTO;

    /**
     * Create a new team
     *
     * @param TeamCreationDTO $teamCreationDTO
     * @return TeamResponseDTO
     */
    public function createTeam(TeamCreationDTO $teamCreationDTO): TeamResponseDTO;

    /**
     * Add a user to a team
     *
     * @param int $teamId
     * @param int $userId
     *
     * @return TeamResponseDTO
     */
    public function addUserToTeam(int $teamId, int $userId): TeamResponseDTO;

    /**
     * Remove a user from a team
     *
     * @param int $teamId
     * @param int $userId
     *
     * @return TeamResponseDTO
     */
    public function removeUserFromTeam(int $teamId, int $userId): TeamResponseDTO;

    /**
     * Assign a project manager to a team
     *
     * @param int $projectManagerId
     * @param int $teamId
     * @return TeamResponseDTO
     */
    public function assignProjectManager(int $projectManagerId, int $teamId): TeamResponseDTO;

    /**
     * Assign a team lead to a team
     *
     * @param int $teamLeadId
     * @param int $teamId
     * @return TeamResponseDTO
     */
    public function assignTeamLead(int $teamLeadId, int $teamId): TeamResponseDTO;

    /**
     * Delete a team
     *
     * @param int $teamId
     */
    public function deleteTeam(int $teamId): void;
}