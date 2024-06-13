<?php

namespace App\EventListener;

use App\Event\LeaveRequestApprovedEvent;
use App\Service\LeaveBalance\LeaveBalanceService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeaveRequestApprovedListener implements EventSubscriberInterface
{
    public function __construct(private readonly LeaveBalanceService $leaveBalanceService)
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            LeaveRequestApprovedEvent::NAME => 'onLeaveRequestApproved',
        ];
    }

    public function onLeaveRequestApproved(LeaveRequestApprovedEvent $event): void
    {
        $leaveRequest = $event->getLeaveRequest();
        $user = $leaveRequest->getUser();
        $days = $leaveRequest->getEndDate()->diff($leaveRequest->getStartDate())->days + 1;

        $this->leaveBalanceService->reduceLeaveBalance($user->getId(), $days);
    }
}