<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Service\Auth\AuthenticationService;
use App\Service\DTO\NotificationDTO;
use App\Service\DTO\UserDTO;
use App\Service\Mapper\MapperService;
use App\Service\Notification\Interface\NotificationServiceInterface;
use App\Service\User\UserQueryService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;

class NotificationService implements NotificationServiceInterface
{
    private const ENTITY_NAME = 'Notification';

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly AuthenticationService $authenticationService,
        private readonly UserQueryService $userQueryService,
        private readonly MapperService $mapperService
    ) {}

    /**
     * @throws ReflectionException
     */
    public function getUserNotifications(): array
    {
        $notifications = $this->notificationRepository->findBy(['user' => $this->authenticationService->getAuthenticatedUser()]);

        return array_map(fn(Notification $notification) => $this->mapperService->mapToDTO($notification), $notifications);
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function sendNotification(NotificationDTO $notificationDTO): void
    {
        $notification = new Notification();
        $notification->setMessage($notificationDTO->getMessage());
        $notification->setUser($this->mapperService->mapToEntity($this->userQueryService->getUserById($notificationDTO->getUserId())));

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

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function sendNotificationToAll(string $message): void
    {
        $users = $this->userQueryService->getUsers();

        foreach ($users as $user) {
            $this->sendNotification(new NotificationDTO($message, $user->getId()));
        }
    }
}