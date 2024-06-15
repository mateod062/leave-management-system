<?php

namespace App\Service\Auth;

use App\Service\Auth\Interface\AuthorizationServiceInterface;
use App\Service\Comment\CommentService;
use App\Service\Mapper\MapperService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly AuthenticationService $authenticationService,
        private readonly CommentService $commentService,
        private readonly MapperService $mapperService
    ) {}

    public function isGranted(string $role): bool
    {
        return $this->authorizationChecker->isGranted($role);
    }

    public function denyAccessUnlessGranted(string $role): void
    {
        if (!$this->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function denyAccessUnlessMemberOfTeam(int $teamId): void
    {
        $user = $this->mapperService->mapToEntity($this->authenticationService->getAuthenticatedUser());

        if (!$user) {
            throw new AccessDeniedHttpException('User not authenticated');
        }

        if ($user->getTeam()->getId() !== $teamId) {
            throw new AccessDeniedHttpException('User not a member of the team');
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws ReflectionException
     */
    public function denyUnlessCommentPoster(int $commentId): void
    {
        $comment = $this->commentService->getCommentById($commentId);
        $user = $this->mapperService->mapToEntity($this->authenticationService->getAuthenticatedUser());

        if (!$user) {
            throw new AccessDeniedHttpException('User not authenticated');
        }

        if ($comment->getUserId() !== $user->getId()) {
            throw new AccessDeniedHttpException('User not the owner of the comment');
        }
    }
}