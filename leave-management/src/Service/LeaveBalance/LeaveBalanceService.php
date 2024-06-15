<?php

namespace App\Service\LeaveBalance;

use App\DTO\LeaveBalanceInitializationDTO;
use App\Repository\LeaveBalanceRepository;
use App\Service\LeaveBalance\Interface\LeaveBalanceServiceInterface;
use App\Service\Mapper\MapperService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use LogicException;
use ReflectionException;

class LeaveBalanceService implements LeaveBalanceServiceInterface
{
    private const INITIAL_BALANCE = 20;
    private const ENTITY_NAME = 'Leave balance';

    public function __construct(
        private readonly LeaveBalanceRepository $leaveBalanceRepository,
        private readonly MapperService $mapperService
    ) {}

    public function getLeaveBalance(int $userId): int
    {
        $leaveBalance = $this->leaveBalanceRepository->findOneBy([
            'user' => $userId,
            'year' => (int) date('Y')
        ]);

        if (!$leaveBalance) {
            throw new EntityNotFoundException(sprintf('%s for user with id %s not found', self::ENTITY_NAME, $userId));
        }

        return $leaveBalance->getBalance();
    }

    public function reduceLeaveBalance(int $userId, int $days): void
    {
        $leaveBalance = $this->leaveBalanceRepository->findOneBy(['user' => $userId]);

        if (!$leaveBalance) {
            throw new EntityNotFoundException(sprintf('%s for user with id %s not found', self::ENTITY_NAME, $userId));
        }

        if ($leaveBalance->getBalance() < $days) {
            throw new LogicException('Insufficient leave balance');
        }

        $leaveBalance->setBalance($leaveBalance->getBalance() - $days);

        $this->leaveBalanceRepository->save($leaveBalance);
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function resetLeaveBalances(): void
    {
        $leaveBalances = $this->leaveBalanceRepository->findLastYearBalances();

        foreach ($leaveBalances as $leaveBalance) {
            $unusedDays = $leaveBalance->getBalance();
            $this->initializeLeaveBalance(userId: $leaveBalance->getUser()->getId(), bonus: $unusedDays);

        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function initializeLeaveBalance(int $userId, ?int $bonus = null): void
    {
        $leaveBalance = $this->leaveBalanceRepository->findOneBy(['user' => $userId]);

        if ($leaveBalance) {
            throw new LogicException(sprintf('%s for user with id %s already exists', self::ENTITY_NAME, $userId));
        }

        $leaveBalance = new LeaveBalanceInitializationDTO(
            userId: $userId,
            balance: self::INITIAL_BALANCE + $bonus ?? 0
        );

        $this->leaveBalanceRepository->save($this->mapperService->mapToEntity($leaveBalance));
    }
}