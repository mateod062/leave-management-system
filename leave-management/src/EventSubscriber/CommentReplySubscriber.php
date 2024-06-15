<?php

namespace App\EventSubscriber;

use App\Event\CommentReplyEvent;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommentReplySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CommentReplyEvent::NAME => 'onCommentReply',
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function onCommentReply(CommentReplyEvent $event): void
    {
        $this->notificationService->notifyCommentReply($event->getComment()->getId());
    }
}