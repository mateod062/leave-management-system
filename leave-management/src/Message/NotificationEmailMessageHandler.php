<?php

namespace App\Message;

use App\Service\Mailer\Interface\MailerServiceInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotificationEmailMessageHandler implements MessageHandlerInterface
{
    private function __construct(
        private readonly MailerServiceInterface $mailerService
    )
    {}

    public function __invoke(NotificationEmailMessage $message): void
    {
        $this->mailerService->sendNotificationEmail(
            $message->getRecipientEmail(),
            $message->getSubject(),
            $message->getTemplate(),
            $message->getContext()
        );
    }
}