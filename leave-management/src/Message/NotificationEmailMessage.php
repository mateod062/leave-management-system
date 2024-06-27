<?php

namespace App\Message;

class NotificationEmailMessage
{
    public function __construct(
        private readonly string $recipientEmail,
        private readonly string $subject,
        private readonly string $template,
        private readonly array $context
    )
    {}

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getContext(): array
    {
        return $this->context;
    }


}