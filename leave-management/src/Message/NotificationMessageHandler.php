<?php

namespace App\Message;

use App\DTO\NotificationDTO;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotificationMessageHandler implements MessageHandlerInterface
{
    private function __construct(
        private readonly NotificationService $notificationService
    )
    {}

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function __invoke(NotificationMessage $message): void
    {
        $this->notificationService->createNotification(new NotificationDTO(
            message: $message->getMessage(),
            userId: $message->getUserId(),
        ));
    }
}