<?php

namespace App\Service\LeaveRequest\Interface;

use App\DTO\LeaveRequestCalendarDTO;
use App\DTO\LeaveRequestFilterDTO;

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
     * Get leave history
     *
     * @param int $userId
     * @return array
     */
    public function getLeaveHistory(int $userId): array;
}