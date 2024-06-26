<?php

namespace App\Service\LeaveRequest;

use App\DTO\LeaveRequestCalendarDTO;
use App\DTO\LeaveRequestDayDTO;
use App\DTO\LeaveRequestDTO;
use App\DTO\LeaveRequestFilterDTO;
use App\Entity\LeaveStatus;
use App\Entity\UserRole;
use App\Repository\LeaveRequestRepository;
use App\Repository\UserRepository;
use App\Service\Auth\Interface\AuthenticationServiceInterface;
use App\Service\LeaveRequest\Interface\LeaveRequestQueryServiceInterface;
use App\Service\Mapper\MapperService;
use App\Service\Team\Interface\TeamServiceInterface;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use LogicException;
use ReflectionException;

class LeaveRequestQueryService implements LeaveRequestQueryServiceInterface
{
    private const ENTITY_NAME = 'Leave request';

    public function __construct(
        private readonly LeaveRequestRepository $leaveRequestRepository,
        private readonly UserRepository         $userRepository,
        private readonly TeamServiceInterface   $teamService,
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly MapperService          $mapperService
    ) {}

    /**
     * @throws ReflectionException
     */
    public function getLeaveRequestsForTeamCalendar(int $teamId, int $month, int $year): LeaveRequestCalendarDTO
    {
        $leaveRequests = $this->leaveRequestRepository->findByTeamAndMonth($teamId, $month, $year);

        $startDate = new DateTime("$year-$month-01");
        $daysInMonth = $startDate->format('t');

        $leaveRequestsByDay = array_fill(1, $daysInMonth, []);

        foreach ($leaveRequests as $leaveRequest) {
            $requestDay = (int)$leaveRequest->getStartDate()->format('j');
            $leaveRequestsByDay[$requestDay][] = $this->mapperService->mapToDTO($leaveRequest);
        }

        $days = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $days[] = new LeaveRequestDayDTO($day, $leaveRequestsByDay[$day]);
        }

        return new LeaveRequestCalendarDTO($startDate->format('F'), $year, $days);
    }

    /**
     * @throws ReflectionException
     */
    public function getOverlappingLeaveRequests(int $userId, DateTime $startDate, DateTime $endDate): array
    {
        $user = $this->userRepository->find($userId);
        $overlappingLeaveRequests = $this->leaveRequestRepository->findOverlapping($user, $startDate, $endDate);

        return array_map(fn($leaveRequest) => $this->mapperService->mapToDTO($leaveRequest), $overlappingLeaveRequests);
    }

    /**
     * @throws ReflectionException
     */
    public function getLeaveRequestById(int $id): LeaveRequestDTO
    {
        $leaveRequest = $this->leaveRequestRepository->find($id);

        if ($leaveRequest === null) {
            throw new EntityNotFoundException(sprintf('%s with id %d not found', self::ENTITY_NAME, $id));
        }

        return $this->mapperService->mapToDTO($leaveRequest);
    }

    /**
     * @throws ReflectionException
     */
    public function getLeaveRequestsForApprover(): array
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $teams = [];

        if ($user->getRole() === UserRole::ROLE_TEAM_LEAD->value) {
            $teams[] = $this->teamService->getLeadingTeam($user->getId());
        }
        elseif ($user->getRole() === UserRole::ROLE_PROJECT_MANAGER->value) {
            $teams = $this->teamService->getManagedTeams($user->getId());
        }
        else {
            throw new LogicException('User is not an approver');
        }

        $leaveRequests = [];

        foreach ($teams as $team) {
            $users = array_merge($leaveRequests, $this->userRepository->findBy(['team' => $team->getId()]));
            $leaveRequests = array_merge($leaveRequests, $this->leaveRequestRepository->findBy(['user' => $users, 'status' => LeaveStatus::PENDING->value]));
        }

        return array_map(fn($leaveRequest) => $this->mapperService->mapToDTO($leaveRequest), $leaveRequests);
    }


    /**
     * @throws ReflectionException
     */
    public function getLeaveRequests(LeaveRequestFilterDTO $filter): array
    {
        $criteria = [];

        if ($filter->getId() !== null) {
            $criteria['id'] = $filter->getId();
        }
        if ($filter->getUserId() !== null) {
            $criteria['user'] = $filter->getUserId();
        }
        if ($filter->getStatus() !== null) {
            $criteria['status'] = $filter->getStatus();
        }
        if ($filter->getStartDate() !== null) {
            $criteria['startDate'] = $filter->getStartDate();
        }
        if ($filter->getEndDate() !== null) {
            $criteria['endDate'] = $filter->getEndDate();
        }

        return array_map(fn($leaveRequest) => $this->mapperService->mapToDTO($leaveRequest), $this->leaveRequestRepository->findBy($criteria));
    }


    public function getLeaveHistory(int $userId): array
    {
        return $this->leaveRequestRepository->findBy(['user' => $userId]);
    }
}