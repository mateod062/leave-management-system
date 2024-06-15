<?php

namespace App\Service\LeaveRequest;

use App\DTO\LeaveRequestCalendarDTO;
use App\DTO\LeaveRequestDayDTO;
use App\DTO\LeaveRequestDTO;
use App\DTO\LeaveRequestFilterDTO;
use App\Repository\LeaveRequestRepository;
use App\Service\LeaveRequest\Interface\LeaveRequestQueryServiceInterface;
use App\Service\Mapper\MapperService;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
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