<?php

namespace App\Controller;

use App\Service\Notification\Interface\NotificationServiceInterface;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    public function __construct(
        private readonly NotificationServiceInterface $notificationService,
    ) {}

    #[Route('/notifications', name: 'get_notifications', methods: ['GET'])]
    public function getNotifications(): JsonResponse
    {
        try {
            return $this->json($this->notificationService->getUserNotifications());
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/notifications', name: 'clear_notifications', methods: ['DELETE'])]
    public function clearNotifications(): JsonResponse
    {
        try {
            $this->notificationService->clearNotifications();
            return $this->json(['message' => 'Notifications cleared successfully']);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/notifications/{notificationId}', name: 'delete_notification', methods: ['DELETE'])]
    public function deleteNotification(Request $request): JsonResponse
    {
        try {
            $this->notificationService->deleteNotification($request->attributes->get('notificationId'));
            return $this->json(['message' => 'Notification deleted successfully']);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}