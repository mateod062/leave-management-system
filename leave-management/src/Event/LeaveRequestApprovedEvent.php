<?php

namespace App\Event;

use App\Entity\LeaveRequest;
use Symfony\Contracts\EventDispatcher\Event;

class LeaveRequestApprovedEvent extends Event
{
    public const NAME = 'leave_request.approved';

    public function __construct(
        private readonly LeaveRequest $leaveRequest
    ) {}

    public function getLeaveRequest(): LeaveRequest
    {
        return $this->leaveRequest;
    }
}