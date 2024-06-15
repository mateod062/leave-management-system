<?php

namespace App\Event;

use App\Entity\LeaveRequest;
use Symfony\Contracts\EventDispatcher\Event;

class LeaveRequestRejectedEvent extends Event
{
    public const NAME = 'leave.request.rejected';
    public function __construct(
        private readonly LeaveRequest $leaveRequest
    )
    {}

    public function getLeaveRequest(): LeaveRequest
    {
        return $this->leaveRequest;
    }
}