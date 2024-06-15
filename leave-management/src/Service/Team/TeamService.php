<?php

namespace App\Service\Team;

use App\DTO\TeamCreationDTO;
use App\DTO\TeamResponseDTO;
use App\DTO\UserResponseDTO;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\LeaveBalance\LeaveBalanceService;
use App\Service\Mapper\MapperService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use LogicException;
use ReflectionException;

class TeamService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MapperService $mapperService,
        private readonly TeamRepository $teamRepository,
        private readonly LeaveBalanceService $leaveBalanceService
    ){}


    /**
     * @throws ReflectionException
     */
    public function getTeamMembers(int $teamId): array
    {
        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            throw new EntityNotFoundException(sprintf('Team with id %s not found', $teamId));
        }

        return array_map(fn(User $user) => $this->mapperService->mapToDTO($user, UserResponseDTO::class), $team->getMembers()->toArray());
    }

    /**
     * @throws ReflectionException
     */
    public function getProjectManager(int $teamId): UserResponseDTO
    {
        $projectManager = $this->userRepository->findOneBy(['team' => $teamId, 'role' => UserRole::ROLE_PROJECT_MANAGER->value]);

        if (!$projectManager) {
            throw new EntityNotFoundException(sprintf('Project Manager for team with id %s not found', $teamId));
        }

        $projectManagerDTO = $this->mapperService->mapToDTO($projectManager, UserResponseDTO::class);
        $projectManagerDTO->setLeaveBalance($this->leaveBalanceService->getLeaveBalance($projectManager->getId()));

        return $projectManagerDTO;
    }

    /**
     * @throws ReflectionException
     */
    public function getTeamLead(int $teamId): UserResponseDTO
    {
        $teamLead = $this->userRepository->findOneBy(['team' => $teamId, 'role' => UserRole::ROLE_TEAM_LEAD->value]);

        if (!$teamLead) {
            throw new EntityNotFoundException(sprintf('Team Lead for team with id %s not found', $teamId));
        }

        $teamLeadDTO = $this->mapperService->mapToDTO($teamLead, UserResponseDTO::class);
        $teamLeadDTO->setLeaveBalance($this->leaveBalanceService->getLeaveBalance($teamLead->getId()));

        return $teamLeadDTO;
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function createTeam(TeamCreationDTO $teamCreationDTO): TeamResponseDTO
    {
        if ($teamCreationDTO->getProjectManagerId() === null) {
            throw new LogicException('Project Manager is required');
        }

        if ($teamCreationDTO->getTeamLeadId() === null) {
            throw new LogicException('Team Lead is required');
        }

        if ($teamCreationDTO->getProjectManagerId() === $teamCreationDTO->getTeamLeadId()) {
            throw new LogicException('Project Manager and Team Lead cannot be the same person');
        }

        if ($this->teamRepository->findOneBy(['name' => $teamCreationDTO->getName()])) {
            throw new LogicException('Team with this name already exists');
        }

        $team = $this->mapperService->mapToEntity($teamCreationDTO, Team::class);

        return $this->mapperService->mapToDTO($this->teamRepository->save($team), TeamResponseDTO::class);
    }

    /**
     * @throws ReflectionException
     */
    public function addUserToTeam(int $teamId, int $userId): TeamResponseDTO
    {
        $team = $this->teamRepository->find($teamId);
        $user = $this->userRepository->find($userId);

        if (!$team) {
            throw new EntityNotFoundException(sprintf('Team with id %s not found', $teamId));
        }

        if (!$user) {
            throw new EntityNotFoundException(sprintf('User with id %s not found', $userId));
        }

        if ($user->getTeam()) {
            throw new LogicException('User already belongs to a team');
        }

        if ($user->getRole() === UserRole::ROLE_PROJECT_MANAGER->value) {
            throw new LogicException('Project Manager already exists in this team');
        }

        if ($user->getRole() === UserRole::ROLE_TEAM_LEAD->value) {
            throw new LogicException('Team Lead already exists in this team');
        }

        if ($user->getRole() === UserRole::ROLE_ADMIN->value) {
            throw new LogicException('Admin cannot be added to a team');
        }

        $team->getMembers()->add($user);

        return $this->mapperService->mapToDTO($this->teamRepository->save($team), TeamResponseDTO::class);
    }

    /**
     * @throws ReflectionException
     */
    public function assignProjectManager(int $projectManagerId, int $teamId): TeamResponseDTO
    {
        $projectManager = $this->userRepository->find($projectManagerId);

        if (!$projectManager) {
            throw new EntityNotFoundException(sprintf('User with id %s not found', $projectManagerId));
        }

        if ($projectManager->getRole() !== UserRole::ROLE_PROJECT_MANAGER->value) {
            throw new LogicException('User is not a Project Manager');
        }

        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            throw new LogicException(sprintf('Team with id %s not found', $teamId));
        }

        $team->setProjectManager($projectManager);

        return $this->mapperService->mapToDTO($this->teamRepository->save($team), TeamResponseDTO::class);
    }

    /**
     * @throws ReflectionException
     */
    public function assignTeamLead(int $teamLeadId, int $teamId): TeamResponseDTO
    {
$teamLead = $this->userRepository->find($teamLeadId);

        if (!$teamLead) {
            throw new EntityNotFoundException(sprintf('User with id %s not found', $teamLeadId));
        }

        if ($teamLead->getRole() !== UserRole::ROLE_TEAM_LEAD->value) {
            throw new LogicException('User is not a Team Lead');
        }

        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            throw new LogicException(sprintf('Team with id %s not found', $teamId));
        }

        $team->setTeamLead($teamLead);

        return $this->mapperService->mapToDTO($this->teamRepository->save($team), TeamResponseDTO::class);
    }

    /**
     * @throws ReflectionException
     */
    public function removeUserFromTeam(int $teamId, int $userId): TeamResponseDTO {
        $team = $this->teamRepository->find($teamId);
        $user = $this->userRepository->find($userId);

        if (!$team) {
            throw new EntityNotFoundException(sprintf('Team with id %s not found', $teamId));
        }

        if (!$user) {
            throw new EntityNotFoundException(sprintf('User with id %s not found', $userId));
        }

        if (!$team->getMembers()->contains($user)) {
            throw new LogicException('User does not belong to this team');
        }

        if ($team->getProjectManager() === $user) {
            throw new LogicException('Project Manager cannot be removed from the team');
        }

        if ($team->getTeamLead() === $user) {
            throw new LogicException('Team Lead cannot be removed from the team');
        }

        $team->getMembers()->removeElement($user);

        return $this->mapperService->mapToDTO($this->teamRepository->save($team), TeamResponseDTO::class);
    }

    public function deleteTeam(int $teamId): void
    {
        $team = $this->teamRepository->find($teamId);

        if (!$team) {
            throw new EntityNotFoundException(sprintf('Team with id %s not found', $teamId));
        }

        $this->teamRepository->delete($team);
    }
}