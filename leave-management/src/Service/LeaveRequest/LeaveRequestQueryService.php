<?php

namespace App\Service\LeaveRequest;

use App\Repository\LeaveRequestRepository;
use App\Service\DTO\LeaveRequestCalendarDTO;
use App\Service\DTO\LeaveRequestDayDTO;
use App\Service\DTO\LeaveRequestFilterDTO;
use App\Service\LeaveRequest\Interface\LeaveRequestQueryServiceInterface;
use App\Service\Mapper\MapperService;
use DateTime;
use ReflectionException;

class LeaveRequestQueryService implements LeaveRequestQueryServiceInterface
{
    private const ENTITY_NAME = 'Leave request';

    public function __construct(
        private readonly LeaveRequestRepository $leaveRequestRepository,
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

        return $this->leaveRequestRepository->findBy($criteria);
    }

    public function getLeaveHistory(int $userId): array
    {
        return $this->getLeaveRequests(new LeaveRequestFilterDTO($userId));
    }
}