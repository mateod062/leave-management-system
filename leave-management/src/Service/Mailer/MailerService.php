<?php

namespace App\Service\Mailer;

use App\Service\Mailer\Interface\MailerServiceInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerService implements MailerServiceInterface
{
    private function __construct(
        private readonly MailerInterface $mailer
    )
    {}

    /**
     * @throws TransportExceptionInterface
     */
    public function sendNotificationEmail(string $recipientEmail, string $subject, string $template, array $context): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@example.com', 'Leave Management'))
            ->to($recipientEmail)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        $this->mailer->send($email);
    }
}