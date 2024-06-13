<?php

namespace App\Command;

use App\Service\LeaveBalance\LeaveBalanceService;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResetLeaveBalancesCommand extends Command
{
    protected static $defaultName = 'app:reset-leave-balances';

    public function __construct(private readonly LeaveBalanceService $leaveBalanceService)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Reset leave balances for all users');
    }

    /**
     * @throws \ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->leaveBalanceService->resetLeaveBalances();

        $io->success('Leave balances reset successfully');

        return Command::SUCCESS;
    }
}