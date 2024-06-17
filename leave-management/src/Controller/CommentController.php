<?php

namespace App\Controller;

use App\DTO\CommentDTO;
use App\Service\Auth\Interface\AuthenticationServiceInterface;
use App\Service\Auth\Interface\AuthorizationServiceInterface;
use App\Service\Comment\Interface\CommentServiceInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    public function __construct(
        private readonly CommentServiceInterface $commentService,
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly AuthorizationServiceInterface $authorizationService
    ) {}

    #[Route(path: '/comments/{leaveRequestId}', name: 'get_comments', methods: ['GET'])]
    public function getComments(Request $request): JsonResponse
    {
        return $this->json($this->commentService->getComments($request->attributes->get('leaveRequestId')));
    }

    #[Route(path: '/comments/{leaveRequestId}', name: 'post_comment', methods: ['POST'])]
    public function postComment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $comment = $this->commentService->addComment(new CommentDTO(
                userId: $this->authenticationService->getAuthenticatedUser()->getId(),
                leaveRequestId: $request->attributes->get('leaveRequestId'),
                comment: $data['comment']
            ));

            return $this->json($comment);
        } catch (OptimisticLockException|ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (ORMException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/comments/reply/{parentCommentId}', name: 'update_comment', methods: ['PUT'])]
    public function reply(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $parentComment = $this->commentService->getCommentById($request->attributes->get('parentCommentId'));

            $comment = $this->commentService->addComment(new CommentDTO(
                userId: $this->authenticationService->getAuthenticatedUser()->getId(),
                leaveRequestId: $parentComment->getLeaveRequestId(),
                parentCommentId: $parentComment->getId(),
                comment: $data['comment']
            ));

            return $this->json($comment);
        } catch (OptimisticLockException|ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (ORMException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/comments/{commentId}', name: 'edit_comment', methods: ['PUT'])]
    public function editComment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $comment = $this->commentService->getCommentById($request->attributes->get('commentId'));

            if ($comment->getUserId() !== $this->authenticationService->getAuthenticatedUser()->getId()) {
                return $this->json(['error' => 'You are not authorized to edit this comment'], Response::HTTP_UNAUTHORIZED);
            }

            $comment->setComment($data['comment']);
            $comment = $this->commentService->updateComment($comment);

            return $this->json($comment);
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/comments/{commentId}', name: 'delete_comment', methods: ['DELETE'])]
    public function deleteComment(Request $request): JsonResponse
    {
        try {
            $comment = $this->commentService->getCommentById($request->attributes->get('commentId'));

            $this->commentService->deleteComment($comment->getId());

            return $this->json(['message' => 'Comment deleted']);
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}