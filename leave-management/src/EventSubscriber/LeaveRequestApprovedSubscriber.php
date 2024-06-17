<?php

namespace App\EventSubscriber;

use App\Event\LeaveRequestApprovedEvent;
use App\Service\LeaveBalance\LeaveBalanceService;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeaveRequestApprovedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LeaveBalanceService $leaveBalanceService,
        private readonly NotificationService $notificationService
    )
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            LeaveRequestApprovedEvent::NAME => 'onLeaveRequestApproved',
        ];
    }

    /**
     * @throws \ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onLeaveRequestApproved(LeaveRequestApprovedEvent $event): void
    {
        $leaveRequest = $event->getLeaveRequest();
        $user = $leaveRequest->getUser();
        $days = $leaveRequest->getEndDate()->diff($leaveRequest->getStartDate())->days + 1;

        $this->leaveBalanceService->reduceLeaveBalance($user->getId(), $days);
        $this->notificationService->notifyLeaveRequestApproved($leaveRequest->getId());
    }
}