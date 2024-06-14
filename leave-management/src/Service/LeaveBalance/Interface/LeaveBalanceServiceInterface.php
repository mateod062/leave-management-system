<?php

namespace App\Service\LeaveBalance\Interface;

interface LeaveBalanceServiceInterface
{
    /**
     * Get leave balance for a user
     *
     * @param int $userId
     *
     * @return int
     */
    public function getLeaveBalance(int $userId): int;

    /**
     * Reduce leave balance for a user
     *
     * @param int $userId
     * @param int $days
     *
     * @return void
     */
    public function reduceLeaveBalance(int $userId, int $days): void;

    /**
     * Reset leave balances for all users, adding unused leave days to the next year
     *
     * @return void
     */
    public function resetLeaveBalances(): void;
}