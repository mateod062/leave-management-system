<?php

namespace App\EventSubscriber;

use App\Event\CommentPostedEvent;
use App\Event\CommentReplyEvent;
use App\Service\Notification\NotificationService;
use App\Service\User\UserQueryService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CommentPostedEvent::NAME => 'onCommentPosted',
            CommentReplyEvent::NAME => 'onCommentReply',
        ];
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onCommentPosted(CommentPostedEvent $event): void
    {
        $user = $event->getComment()->getLeaveRequest()->getUser();

        $this->notificationService->notifyCommentPosted($event->getComment()->getId());
        $this->notificationService->sendEmailNotification(
            $user,
            'New comment posted on your leave request',
            'email/comment_posted.html.twig',
            ['comment' => $event->getComment()]
        );
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function onCommentReply(CommentReplyEvent $event): void
    {
        $user = $event->getComment()->getParentComment()->getUser();

        $this->notificationService->notifyCommentReply($event->getComment()->getId());
        $this->notificationService->sendEmailNotification(
            $user,
            'Someone replied to your comment',
            'email/comment_reply.html.twig',
            ['comment' => $event->getComment()]
        );
    }
}