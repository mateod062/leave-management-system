<?php

namespace App\Entity;

use App\Repository\LeaveRequestRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: LeaveRequestRepository::class)]
class LeaveRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[NotNull]
    private User $user;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[NotNull]
    private DateTime $startDate;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[NotNull]
    private DateTime $endDate;

    #[ORM\Column(type: Types::TEXT, nullable: false, enumType: LeaveStatus::class)]
    #[NotNull]
    private LeaveStatus $status;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private string $reason;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $teamLeaderApproval = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $projectManagerApproval = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTime $createdAt;

    public function __construct()
    {
        $this->status = LeaveStatus::PENDING;
        $this->createdAt = new DateTime();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getStatus(): LeaveStatus
    {
        return $this->status;
    }

    public function setStatus(LeaveStatus $status): void
    {
        $this->status = $status;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    public function teamLeaderApproved(): bool
    {
        return $this->teamLeaderApproval;
    }

    public function setTeamLeaderApproval(bool $teamLeaderApproval): void
    {
        $this->teamLeaderApproval = $teamLeaderApproval;
    }

    public function projectManagerApproved(): bool
    {
        return $this->projectManagerApproval;
    }

    public function setProjectManagerApproval(bool $projectManagerApproval): void
    {
        $this->projectManagerApproval = $projectManagerApproval;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
