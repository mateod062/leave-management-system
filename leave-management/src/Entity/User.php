<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[NotBlank]
    #[Unique]
    private string $username;

    #[ORM\Column(length: 100, unique: true)]
    #[Email]
    #[Unique]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(type: "user_role")]
    private UserRole $role;

    #[ORM\OneToOne(targetEntity: Team::class, inversedBy: "teamLead")]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?Team $leadingTeam;

    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: "projectManager")]
    private Collection $managedTeams;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: "members")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Team $team;

    public function __construct()
    {
        $this->managedTeams = new ArrayCollection();
    }

    public function getLeadingTeam(): ?Team
    {
        return $this->leadingTeam;
    }

    public function setLeadingTeam(?Team $leadingTeam): void
    {
        if ($this->leadingTeam !== $leadingTeam) {
            $this->leadingTeam = $leadingTeam;
            $leadingTeam?->setTeamLead($this);
        }
    }

    public function getManagedTeams(): Collection
    {
        return $this->managedTeams;
    }

    public function setManagedTeams(Collection $managedTeams): void
    {
        foreach ($this->managedTeams as $managedTeam) {
            if (!$managedTeams->contains($managedTeam)) {
                $managedTeam->setProjectManager(null);
            }
        }

        $this->managedTeams = $managedTeams;

        foreach ($managedTeams as $managedTeam) {
            $managedTeam->setProjectManager($this);
        }
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        if ($this->team !== $team) {
            $this->team = $team;
            $team?->addMember($this);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getRole(): string
    {
        return $this->role->value;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getRoles(): array
    {
        return [$this->role->value];
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
