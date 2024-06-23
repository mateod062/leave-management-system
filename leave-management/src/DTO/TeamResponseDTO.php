<?php

namespace App\DTO;

class TeamResponseDTO
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        /**
         * @var UserDTO[]
         */
        private array $members = []
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function setMembers(array $members): void
    {
        $this->members = $members;
    }
}