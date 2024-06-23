<?php

namespace App\DTO;

class TeamCreationDTO
{
    public function __construct(
        private ?int    $id = null,
        private ?string $name = null,
        /**
         * @var int[]
         */
        private array   $membersIds = [],
        private ?int    $teamLeadId = null,
        private ?int    $projectManagerId = null
    )
    {}

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getMembersIds(): array
    {
        return $this->membersIds;
    }

    public function setMembersIds(array $membersIds): void
    {
        $this->membersIds = $membersIds;
    }

    public function getTeamLeadId(): ?int
    {
        return $this->teamLeadId;
    }

    public function setTeamLeadId(?int $teamLeadId): void
    {
        $this->teamLeadId = $teamLeadId;
    }

    public function getProjectManagerId(): ?int
    {
        return $this->projectManagerId;
    }

    public function setProjectManagerId(?int $projectManagerId): void
    {
        $this->projectManagerId = $projectManagerId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }
}