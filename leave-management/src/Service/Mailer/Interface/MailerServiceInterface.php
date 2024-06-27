<?php

namespace App\Service\Mailer\Interface;

interface MailerServiceInterface
{
    /**
     * Send an email notification to the recipient
     *
     * @param string $recipientEmail
     * @param string $subject
     * @param string $template
     * @param array $context
     * @return void
     */
    public function sendNotificationEmail(string $recipientEmail, string $subject, string $template, array $context): void;
}