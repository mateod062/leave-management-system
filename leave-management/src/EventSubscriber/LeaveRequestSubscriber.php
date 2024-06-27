<?php

namespace App\EventSubscriber;

use App\Event\LeaveRequestApprovedEvent;
use App\Event\LeaveRequestCreatedEvent;
use App\Event\LeaveRequestRejectedEvent;
use App\Service\LeaveBalance\LeaveBalanceService;
use App\Service\Notification\NotificationService;
use Carbon\Carbon;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeaveRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LeaveBalanceService $leaveBalanceService,
        private readonly NotificationService $notificationService
    )
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            LeaveRequestCreatedEvent::NAME => 'onLeaveRequestCreated',
            LeaveRequestApprovedEvent::NAME => 'onLeaveRequestApproved',
            LeaveRequestRejectedEvent::NAME => 'onLeaveRequestRejected',
        ];
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onLeaveRequestCreated(LeaveRequestCreatedEvent $event): void
    {
        $leaveRequest = $event->getLeaveRequest();
        $user = $leaveRequest->getUser();
        $projectManager = $user->getTeam()->getProjectManager();
        $teamLead = $user->getTeam()->getTeamLead();
        $this->notificationService->notifyLeaveRequestCreated($leaveRequest->getId());
        $this->notificationService->sendEmailNotification(
            $projectManager,
            'New leave request posted',
            'email/leave_request_created.html.twig',
            [
                'leaveRequest' => $leaveRequest,
                'recipient' => $projectManager
            ]
        );
        $this->notificationService->sendEmailNotification(
            $teamLead,
            'New leave request posted',
            'email/leave_request_created.html.twig',
            [
                'leaveRequest' => $leaveRequest,
                'recipient' => $teamLead
            ]
        );
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onLeaveRequestApproved(LeaveRequestApprovedEvent $event): void
    {
        $leaveRequest = $event->getLeaveRequest();
        $user = $leaveRequest->getUser();
        $startDate = Carbon::instance($leaveRequest->getStartDate());
        $endDate = Carbon::instance($leaveRequest->getEndDate());
        $days = $endDate->diffInWeekdays($startDate) + 1;

        $this->leaveBalanceService->reduceLeaveBalance($user->getId(), $days);
        $this->notificationService->notifyLeaveRequestApproved($leaveRequest->getId());
        $this->notificationService->sendEmailNotification(
            $user,
            'Your leave request has been approved',
            'email/leave_request_approved.html.twig',
            ['leaveRequest' => $leaveRequest]
        );
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function onLeaveRequestRejected(LeaveRequestRejectedEvent $event): void
    {
        $this->notificationService->notifyLeaveRequestRejected($event->getLeaveRequest()->getId());
        $this->notificationService->sendEmailNotification(
            $event->getLeaveRequest()->getUser(),
            'Your leave request has been rejected',
            'email/leave_request_rejected.html.twig',
            ['leaveRequest' => $event->getLeaveRequest()]
        );
    }
}