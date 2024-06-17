<?php

namespace App\EventSubscriber;

use App\Event\LeaveRequestCreatedEvent;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeaveRequestCreatedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly NotificationService $notificationService
    )
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            LeaveRequestCreatedEvent::NAME => 'onLeaveRequestCreated',
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
        $this->notificationService->notifyLeaveRequestCreated($leaveRequest->getId());
    }
}