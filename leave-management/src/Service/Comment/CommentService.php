<?php

namespace App\Service\Comment;

use App\DTO\CommentDTO;
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

    public function getComments(int $leaveRequestId): array
    {
        return $this->commentRepository->findBy(['leaveRequestId' => $leaveRequestId]);
    }

    /**
     * @throws ReflectionException
     */
    public function getCommentById(int $commentId): CommentDTO
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY, $commentId));
        }

        return $this->mapperService->mapToDTO($comment);
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function addComment(CommentDTO $comment): CommentDTO
    {
        $commentEntity = $this->mapperService->mapToEntity($comment);
        $this->commentRepository->save($commentEntity);

        if (!$comment->getParentCommentId()) {
            $this->eventDispatcher->dispatch(new CommentPostedEvent($commentEntity), CommentPostedEvent::NAME);
        } else {
            $this->eventDispatcher->dispatch(new CommentReplyEvent($commentEntity), CommentReplyEvent::NAME);
        }

        return $this->mapperService->mapToDTO($commentEntity);
    }

    /**
     * @throws ReflectionException
     */
    public function updateComment(CommentDTO $comment): CommentDTO
    {
        $commentEntity = $this->commentRepository->find($comment->getId());
        if ($commentEntity === null) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY, $comment->getId()));
        }
        $commentEntity->setComment($comment->getComment());
        $commentEntity->setCreatedAt($comment->getCreatedAt());

        return $this->mapperService->mapToDTO($this->commentRepository->save($commentEntity));
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->commentRepository->find($commentId);
        if ($comment === null) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY, $commentId));
        }
        $this->commentRepository->delete($comment);
    }
}