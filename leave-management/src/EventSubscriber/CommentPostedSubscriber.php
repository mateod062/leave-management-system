<?php

namespace App\EventSubscriber;

use App\Event\CommentPostedEvent;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommentPostedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CommentPostedEvent::NAME => 'onCommentPosted',
        ];
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onCommentPosted(CommentPostedEvent $event): void
    {
        $this->notificationService->notifyCommentPosted($event->getComment()->getId());
    }
}