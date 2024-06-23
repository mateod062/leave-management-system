<?php

namespace App\DTO;

use DateTimeImmutable;

class CommentResponseDTO
{
    public function __construct(
        private ?int               $id = null,
        private ?int               $userId = null,
        private ?int               $leaveRequestId = null,
        private ?int               $parentCommentId = null,
        private ?string            $message = null,
        private ?DateTimeImmutable $createdAt = null,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getLeaveRequestId(): int
    {
        return $this->leaveRequestId;
    }

    public function setLeaveRequestId(int $leaveRequestId): void
    {
        $this->leaveRequestId = $leaveRequestId;
    }

    public function getParentCommentId(): ?int
    {
        return $this->parentCommentId;
    }

    public function setParentCommentId(?int $parentCommentId): void
    {
        $this->parentCommentId = $parentCommentId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}