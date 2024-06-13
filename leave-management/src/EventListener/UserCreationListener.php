<?php

namespace App\EventListener;

use App\Event\UserCreatedEvent;
use App\Service\LeaveBalance\LeaveBalanceService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCreationListener implements EventSubscriberInterface
{
    public function __construct(private readonly LeaveBalanceService $leaveBalanceService)
    {}
    public static function getSubscribedEvents(): array
    {
        return [
            UserCreatedEvent::NAME => 'onUserCreated',
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function onUserCreated(UserCreatedEvent $event): void
    {
        $user = $event->getUser();
        $this->leaveBalanceService->initializeLeaveBalance($user->getId());
    }
}