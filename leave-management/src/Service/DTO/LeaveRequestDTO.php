<?php

namespace App\Service\DTO;

use DateTime;

class LeaveRequestDTO
{
public function __construct(
        private ?int     $id,
        private int      $userId,
        private DateTime $startDate,
        private DateTime $endDate,
        private ?string  $reason,
        private string   $status,
        private bool     $teamLeaderApproval,
        private bool     $projectManagerApproval,
        private DateTime $createdAt,
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

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function teamLeadApproved(): bool
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
}