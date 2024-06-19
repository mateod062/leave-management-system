<?php

namespace App\DTO;

use App\Entity\LeaveStatus;
use DateTime;
use DateTimeImmutable;

class LeaveRequestDTO
{
public function __construct(
        private ?int     $id = null,
        private ?int      $userId = null,
        private ?DateTime $startDate = null,
        private ?DateTime $endDate = null,
        private ?string  $reason = null,
        private string   $status = LeaveStatus::PENDING->value,
        private ?bool     $teamLeaderApproval = null,
        private ?bool     $projectManagerApproval = null,
        private DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function teamLeadApproved(): ?bool
    {
        return $this->teamLeaderApproval;
    }

    public function setTeamLeaderApproval(?bool $teamLeaderApproval): void
    {
        $this->teamLeaderApproval = $teamLeaderApproval;
    }

    public function projectManagerApproved(): ?bool
    {
        return $this->projectManagerApproval;
    }

    public function setProjectManagerApproval(?bool $projectManagerApproval): void
    {
        $this->projectManagerApproval = $projectManagerApproval;
    }
}