<?php

namespace App\Service;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Service\DTO\NotificationDTO;
use App\Service\DTO\UserDTO;
use Doctrine\ORM\EntityNotFoundException;

class NotificationService
{
    private const ENTITY_NAME = 'Notification';

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly AuthenticationService $authenticationService,
        private readonly UserFetchService $userFetchService
    ) {}

    public function getUserNotifications(): array
    {
        $notifications = $this->notificationRepository->findBy(['user' => $this->authenticationService->getAuthenticatedUser()]);

        return array_map(fn(Notification $notification) => NotificationDTO::fromEntity($notification), $notifications);
    }

    public function sendNotification(NotificationDTO $notificationDTO): void
    {
        $notification = new Notification();
        $notification->setMessage($notificationDTO->getMessage());
        $notification->setUser(UserDTO::toDTO($this->userFetchService->getUserById($notificationDTO->getUserId())));

        $this->notificationRepository->save($notification);
    }

    public function clearNotifications(): void
    {
        $notifications = $this->getUserNotifications();

        foreach ($notifications as $notification) {
            $this->notificationRepository->delete($notification);
        }
    }

    public function deleteNotification(int $id): void
    {
        $notification = $this->notificationRepository->find($id);

        if (!$notification) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $id));
        }

        $this->notificationRepository->delete($notification);
    }
}