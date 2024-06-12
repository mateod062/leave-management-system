<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\ManyToOne(targetEntity: LeaveRequest::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private LeaveRequest $leaveRequest;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    #[NotBlank]
    private string $comment;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    #[NotBlank]
    private DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: Comment::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private Comment $parentComment;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getLeaveRequest(): LeaveRequest
    {
        return $this->leaveRequest;
    }

    public function setLeaveRequest(LeaveRequest $leaveRequest): void
    {
        $this->leaveRequest = $leaveRequest;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getParentComment(): Comment
    {
        return $this->parentComment;
    }

    public function setParentComment(Comment $parentComment): void
    {
        $this->parentComment = $parentComment;
    }

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
