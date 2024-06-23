<?php

namespace App\Service\Notification;

use App\DTO\NotificationDTO;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Service\Auth\AuthenticationService;
use App\Service\Comment\CommentService;
use App\Service\LeaveRequest\LeaveRequestQueryService;
use App\Service\Mapper\MapperService;
use App\Service\Notification\Interface\NotificationServiceInterface;
use App\Service\Team\TeamService;
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
        private readonly LeaveRequestQueryService $leaveRequestQueryService,
        private readonly UserQueryService $userQueryService,
        private readonly CommentService $commentService,
        private readonly TeamService $teamService,
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
        $notification = $this->mapperService->mapToEntity($notificationDTO);

        $this->notificationRepository->save($notification);
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function sendNotificationTo(string $message, array $users): void
    {
        foreach ($users as $user) {
            $this->sendNotification(new NotificationDTO(message: $message, userId: $user));
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function notifyLeaveRequestCreated(int $leaveRequestId): void
    {
        $leaveRequest = $this->leaveRequestQueryService->getLeaveRequestById($leaveRequestId);
        $user = $this->userQueryService->getUserById($leaveRequest->getUserId());

        if ($user->getTeamId() === null) {
            return;
        }


        $projectManager = $this->teamService->getProjectManager($user->getTeamId());
        $teamLead = $this->teamService->getTeamLead($user->getTeamId());

        $leaveRequestMembers = [
            $projectManager->getId(),
            $teamLead->getId()
            ];

        $message = sprintf('%s posted a new leave request', $user->getUsername());
        $this->sendNotificationTo($message, $leaveRequestMembers);
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function notifyLeaveRequestApproved(int $leaveRequestId): void
    {
        $leaveRequest = $this->leaveRequestQueryService->getLeaveRequestById($leaveRequestId);
        $message = 'Your leave request has been approved';

        $this->sendNotification(new NotificationDTO($message, $leaveRequest->getUserId()));
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function notifyLeaveRequestRejected(int $leaveRequestId): void
    {
        $leaveRequest = $this->leaveRequestQueryService->getLeaveRequestById($leaveRequestId);
        $message = 'Your leave request has been rejected';

        $this->sendNotification(new NotificationDTO($message, $leaveRequest->getUserId()));
    }

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function notifyCommentPosted(int $commentId): void
    {
        $comment = $this->commentService->getCommentById($commentId);
        $leaveRequest = $this->leaveRequestQueryService->getLeaveRequestById($comment->getLeaveRequestId());
        $commentPoster = $this->userQueryService->getUserById($comment->getUserId());
        $message = sprintf('%s posted a comment on your leave request', $commentPoster->getUsername());
        $this->sendNotification(new NotificationDTO($message, $leaveRequest->getUserId()));
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws ReflectionException
     */
    public function notifyCommentReply(int $commentId): void
    {
        $comment = $this->commentService->getCommentById($commentId);
        $parentComment = $this->commentService->getCommentById($comment->getParentCommentId());
        $commentPoster = $this->userQueryService->getUserById($comment->getUserId());

        $replyMessage = sprintf('%s replied to your comment', $commentPoster->getUsername());

        $this->notifyCommentPosted($commentId);
        $this->sendNotification(new NotificationDTO($replyMessage, $parentComment->getUserId()));
    }

    /**
     * @throws ReflectionException
     */
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