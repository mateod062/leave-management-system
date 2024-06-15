<?php

namespace App\EventSubscriber;

use App\Event\LeaveRequestRejectedEvent;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeaveRequestRejectedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly NotificationService $notificationService
    )
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            LeaveRequestRejectedEvent::NAME => 'onLeaveRequestRejected',
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function onLeaveRequestRejected(LeaveRequestRejectedEvent $event): void
    {
        $this->notificationService->notifyLeaveRequestRejected($event->getLeaveRequest()->getId());
    }
}