<?php

namespace App\Service\LeaveRequest\Interface;

use App\DTO\LeaveRequestCalendarDTO;
use App\DTO\LeaveRequestDTO;
use App\DTO\LeaveRequestFilterDTO;
use DateTime;

interface LeaveRequestQueryServiceInterface
{
    /**
     * Get leave requests for team calendar
     *
     * @param int $teamId
     * @param int $month
     * @param int $year
     * @return LeaveRequestCalendarDTO
     */
    public function getLeaveRequestsForTeamCalendar(int $teamId, int $month, int $year): LeaveRequestCalendarDTO;

    /**
     * Get leave requests
     *
     * @param LeaveRequestFilterDTO $filter
     * @return array
     */
    public function getLeaveRequests(LeaveRequestFilterDTO $filter): array;

    /**
     * Get overlapping approved leave requests
     *
     * @param int $userId
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return array
     */
    public function getOverlappingLeaveRequests(int $userId, DateTime $startDate, DateTime $endDate): array;

    /**
     * Get leave request by id
     *
     * @param int $id
     * @return LeaveRequestDTO
     */
    public function getLeaveRequestById(int $id): LeaveRequestDTO;

    /**
     * Get leave history
     *
     * @param int $userId
     * @return array
     */
    public function getLeaveHistory(int $userId): array;
}