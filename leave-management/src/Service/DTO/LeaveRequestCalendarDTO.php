<?php

namespace App\Service\DTO;

class LeaveRequestCalendarDTO
{
    public function __construct(
        private int $year,
        private int $month,
        private array $days
    ) {}

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(int $month): void
    {
        $this->month = $month;
    }

    public function getDays(): array
    {
        return $this->days;
    }

    public function setDays(array $days): void
    {
        $this->days = $days;
    }


}