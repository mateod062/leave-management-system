<?php

namespace App\Event;

use App\Entity\LeaveRequest;
use Symfony\Contracts\EventDispatcher\Event;

class LeaveRequestCreatedEvent extends Event
{
    public const NAME = 'leave.request.created';
    public function __construct(
        private readonly LeaveRequest $leaveRequest
    )
    {}

    public function getLeaveRequest(): LeaveRequest
    {
        return $this->leaveRequest;
    }
}