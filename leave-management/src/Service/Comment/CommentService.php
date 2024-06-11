<?php

namespace App\Service\Comment;

use App\Repository\CommentRepository;
use App\Service\Comment\Interface\CommentServiceInterface;
use App\Service\DTO\CommentDTO;
use App\Service\Mapper\MapperService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionException;

class CommentService implements CommentServiceInterface
{
    private const ENTITY = 'Comment';

    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly MapperService $mapperService

    ) {}

    public function getComments(int $leaveRequestId): array
    {
        return $this->commentRepository->findBy(['leaveRequestId' => $leaveRequestId]);
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