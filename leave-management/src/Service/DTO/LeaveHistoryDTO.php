<?php

namespace App\Service\DTO;

class LeaveHistoryDTO
{
    public function __construct(private int $userId, public array $leaveRequests) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getLeaveRequests(): array
    {
        return $this->leaveRequests;
    }

    public function setLeaveRequests(array $leaveRequests): void
    {
        $this->leaveRequests = $leaveRequests;
    }
}