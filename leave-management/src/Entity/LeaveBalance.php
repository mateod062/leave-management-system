<?php

namespace App\Entity;

use App\Repository\LeaveBalanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

#[ORM\Entity(repositoryClass: LeaveBalanceRepository::class)]
class LeaveBalance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    #[NotNull]
    #[PositiveOrZero]
    private int $balance;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    #[NotNull]
    private int $year;

    public function __construct()
    {
        $this->balance = 20;
        $this->year = (int) date('Y');
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): void
    {
        $this->balance = $balance;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
