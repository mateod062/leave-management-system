<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\DTO\UserDTO;
use App\Service\DTO\UserResponseDTO;
use App\Service\LeaveBalance\LeaveBalanceService;
use App\Service\Mapper\MapperService;
use App\Service\User\Interface\UserQueryServiceInterface;
use Doctrine\ORM\EntityNotFoundException;
use ReflectionException;

class UserQueryService implements UserQueryServiceInterface
{
    private const ENTITY_NAME = 'User';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MapperService $mapperService,
        private readonly LeaveBalanceService $leaveBalanceService
    ){}

    /**
     * @throws ReflectionException
     */
    public function getUsers(): array
    {
        $users = $this->userRepository->findAll();

        return array_map(fn(User $user) => $this->mapperService->mapToDTO($user), $users);
    }

    /**
     * @throws ReflectionException
     */
    public function getUserById(int $id): UserDTO
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $id));
        }

        $userDTO = $this->mapperService->mapToDTO($user);
        $userDTO->setLeaveBalance($this->leaveBalanceService->getLeaveBalance($id));

        return $userDTO;
    }

    /**
     * @throws ReflectionException
     */
    public function getUserByEmail(string $email): UserDTO
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('%s with email %s not found', self::ENTITY_NAME, $email));
        }

        $userDTO = $this->mapperService->mapToDTO($user);
        $userDTO->setLeaveBalance($this->leaveBalanceService->getLeaveBalance($userDTO->getId()));

        return $userDTO;
    }
}