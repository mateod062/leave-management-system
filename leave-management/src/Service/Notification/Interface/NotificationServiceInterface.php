<?php

namespace App\Service\Notification\Interface;

use App\Service\DTO\NotificationDTO;

interface NotificationServiceInterface
{
    /**
     * Send a notification to a user
     *
     * @param NotificationDTO $notificationDTO
     * @return void
     */
    public function sendNotification(NotificationDTO $notificationDTO): void;

    /**
     * Send a notification to all users
     *
     * @param string $message
     * @return void
     */
    public function sendNotificationToAll(string $message): void;

    /**
     * Clear all notifications for the user
     *
     * @return void
     */
    public function clearNotifications(): void;

    /**
     * Delete a notification
     *
     * @param int $id
     * @return void
     */
    public function deleteNotification(int $id): void;
}