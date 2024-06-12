<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[NotBlank]
    private string $name;

    #[ORM\OneToOne(targetEntity: Team::class, inversedBy: "leadingTeam")]
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: "CASCADE")]
    #[NotNull]
    private User $teamLead;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "managedTeams")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[NotNull]
    private User $projectManager;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: "team")]
    private Collection $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTeamLead(): User
    {
        return $this->teamLead;
    }

    public function setTeamLead(User $teamLead): void
    {
        $this->teamLead = $teamLead;
    }

    public function getProjectManager(): User
    {
        return $this->projectManager;
    }

    public function setProjectManager(User $projectManager): void
    {
        $this->projectManager = $projectManager;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function setMembers(Collection $members): void
    {
        $this->members = $members;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
