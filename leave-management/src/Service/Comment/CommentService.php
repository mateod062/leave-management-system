<?php

namespace App\Service\Comment;

use App\DTO\CommentCreationDTO;
use App\DTO\CommentResponseDTO;
use App\Entity\Comment;
use App\Event\CommentPostedEvent;
use App\Event\CommentReplyEvent;
use App\Repository\CommentRepository;
use App\Service\Comment\Interface\CommentServiceInterface;
use App\Service\Mapper\MapperService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentService implements CommentServiceInterface
{
    private const ENTITY = 'Comment';

    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly MapperService $mapperService,
        private readonly EventDispatcherInterface $eventDispatcher

    ) {}

    /**
     * @throws ReflectionException
     */
    public function getComments(int $leaveRequestId): array
    {
        $comments = $this->commentRepository->findBy(['leaveRequest' => $leaveRequestId]);
        return array_map(fn($comment) => $this->mapperService->mapToDTO($comment), $comments);
    }

    /**
     * @throws ReflectionException
     */
    public function getCommentById(int $commentId): CommentResponseDTO
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY, $commentId));
        }

        return $this->mapperService->mapToDTO($comment, CommentResponseDTO::class);
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function addComment(CommentCreationDTO $comment): CommentResponseDTO
    {
        $commentEntity = $this->mapperService->mapToEntity($comment, Comment::class);
        $commentEntity = $this->commentRepository->save($commentEntity);

        if (!$comment->getParentCommentId()) {
            $this->eventDispatcher->dispatch(new CommentPostedEvent($commentEntity), CommentPostedEvent::NAME);
        } else {
            $this->eventDispatcher->dispatch(new CommentReplyEvent($commentEntity), CommentReplyEvent::NAME);
        }

        return $this->mapperService->mapToDTO($commentEntity, CommentResponseDTO::class);
    }

    /**
     * @throws ReflectionException
     * @throws ORMException
     */
    public function updateComment(int $id, CommentCreationDTO $comment): CommentResponseDTO
    {
        $commentEntity = $this->commentRepository->find($id);

        if ($commentEntity === null) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY, $id));
        }
        $commentEntity->setMessage($comment->getMessage());
        $commentEntity->setCreatedAt($comment->getCreatedAt());

        return $this->mapperService->mapToDTO($this->commentRepository->save($commentEntity), CommentResponseDTO::class);
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->commentRepository->find($commentId);
        if ($comment === null) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY, $commentId));
        }
        $this->commentRepository->delete($comment);
    }

    /**
     * @throws ReflectionException
     */
    public function getReplies(int $commentId): array
    {
        $replies = $this->commentRepository->findBy(['parentComment' => $commentId]);
        return array_map(fn($comment) => $this->mapperService->mapToDTO($comment), $replies);
    }
}