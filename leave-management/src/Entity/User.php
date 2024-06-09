<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private int $id;

    #[ORM\Column(length: 50, unique: true)]
    #[NotBlank]
    private string $username;

    #[ORM\Column(length: 100, unique: true)]
    #[Email]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(type: "user_role")]
    private UserRole $role;

    #[ORM\OneToOne(targetEntity: Team::class, inversedBy: "teamLead")]
    private Team $leadingTeam;

    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: "projectManager")]
    private array $managedTeams = [];

    #[ORM\OneToOne(targetEntity: Team::class, inversedBy: "members")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Team $team;

    public function getLeadingTeam(): ?Team
    {
        return $this->leadingTeam;
    }

    public function setLeadingTeam(?Team $leadingTeam): void
    {
        $this->leadingTeam = $leadingTeam;
    }

    public function getManagedTeams(): array
    {
        return $this->managedTeams;
    }

    public function setManagedTeams(array $managedTeams): void
    {
        $this->managedTeams = $managedTeams;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $getId): static
    {
        $this->id = $getId;

        return $this;
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
}
