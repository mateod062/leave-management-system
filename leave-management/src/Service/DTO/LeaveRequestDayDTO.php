<?php

namespace App\Service\DTO;

class LeaveRequestDayDTO
{
    public function __construct(
        public int $day,
        public array $leaveRequests
    ) {}
}