<?php

namespace App\Service\LeaveRequest;

use App\Entity\LeaveRequest;
use App\Entity\LeaveStatus;
use App\Entity\UserRole;
use App\Event\LeaveRequestApprovedEvent;
use App\Repository\LeaveRequestRepository;
use App\Service\Auth\AuthenticationService;
use App\Service\DTO\LeaveRequestDTO;
use App\Service\LeaveRequest\Interface\LeaveRequestPersistenceServiceInterface;
use App\Service\Mapper\MapperService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use LogicException;
use ReflectionException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LeaveRequestPersistenceService implements LeaveRequestPersistenceServiceInterface
{
    private const ENTITY_NAME = 'Leave request';
    public function __construct(
        private readonly LeaveRequestRepository $leaveRequestRepository,
        private readonly AuthenticationService  $authenticationService,
        private readonly MapperService          $mapperService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * @throws ReflectionException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function createLeaveRequest(LeaveRequestDTO $leaveRequest): LeaveRequestDTO
    {
        $leaveRequestEntity = $this->mapperService->mapToEntity($leaveRequest);

        $this->leaveRequestRepository->save($leaveRequestEntity);

        return $this->mapperService->mapToDTO($leaveRequestEntity);
    }

    /**
     * @throws ReflectionException
     */
    public function updateLeaveRequest(LeaveRequestDTO $leaveRequestDTO): LeaveRequestDTO
    {
        $leaveRequest = $this->leaveRequestRepository->find($leaveRequestDTO->getId());

        if ($leaveRequest === null) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $leaveRequestDTO->getId()));
        }

        $leaveRequest->setStartDate($leaveRequestDTO->getStartDate());
        $leaveRequest->setEndDate($leaveRequestDTO->getEndDate());
        $leaveRequest->setReason($leaveRequestDTO->getReason());
        $leaveRequest->setStatus(LeaveStatus::tryFrom($leaveRequestDTO->getStatus()));
        $leaveRequest->setTeamLeaderApproval($leaveRequestDTO->teamLeadApproved());
        $leaveRequest->setProjectManagerApproval($leaveRequestDTO->projectManagerApproved());
        $leaveRequest->setCreatedAt($leaveRequestDTO->getCreatedAt());

        $this->leaveRequestRepository->save($leaveRequest);

        return $this->mapperService->mapToDTO($leaveRequest);
    }

    /**
     * @throws ReflectionException
     */
    public function approveLeaveRequest(int $leaveRequestId): void
    {
        $leaveRequest = $this->leaveRequestRepository->find($leaveRequestId);
        $userRole = $this->authenticationService->getAuthenticatedUser()->getRole();

        if ($leaveRequest === null) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $leaveRequestId));
        }

        if ($leaveRequest->getStatus() !== LeaveStatus::PENDING) {
            throw new LogicException('Leave request has been resolved');
        }

        if ($leaveRequest->projectManagerApproved() && $userRole == UserRole::ROLE_PROJECT_MANAGER->value) {
            throw new LogicException('Leave request already approved by project manager');
        }

        if ($leaveRequest->teamLeaderApproved() && $userRole == UserRole::ROLE_TEAM_LEAD->value) {
            throw new LogicException('Leave request already approved by team leader');
        }

        if ($userRole == UserRole::ROLE_TEAM_LEAD->value) {
            $leaveRequest->setTeamLeaderApproval(true);
        }
        elseif ($userRole == UserRole::ROLE_PROJECT_MANAGER->value) {
            $leaveRequest->setProjectManagerApproval(true);
        }

        if ($leaveRequest->projectManagerApproved() && $leaveRequest->teamLeaderApproved()) {
            $leaveRequest->setStatus(LeaveStatus::APPROVED);
            $this->eventDispatcher->dispatch(new LeaveRequestApprovedEvent($leaveRequest), LeaveRequestApprovedEvent::NAME);
        }

        $this->leaveRequestRepository->save($leaveRequest);
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function rejectLeaveRequest(int $leaveRequestId): void
    {
        $leaveRequest = $this->leaveRequestRepository->find($leaveRequestId);
        $userRole = $this->authenticationService->getAuthenticatedUser()->getRole();

        if ($leaveRequest === null) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $leaveRequestId));
        }

        if ($leaveRequest->getStatus() !== LeaveStatus::PENDING) {
            throw new LogicException('Leave request has been resolved');
        }

        if ($leaveRequest->projectManagerApproved() && $userRole == UserRole::ROLE_PROJECT_MANAGER->value) {
            throw new LogicException('Leave request already approved by project manager');
        }

        if ($leaveRequest->teamLeaderApproved() && $userRole == UserRole::ROLE_TEAM_LEAD->value) {
            throw new LogicException('Leave request already approved by team leader');
        }

        $leaveRequest->setStatus(LeaveStatus::REJECTED);
        $this->leaveRequestRepository->save($leaveRequest);
    }

    /**
     * @inheritDoc
     */
    public function deleteLeaveRequest(int $leaveRequestId): void
    {
        $leaveRequest = $this->leaveRequestRepository->find($leaveRequestId);

        if ($leaveRequest === null) {
            throw new EntityNotFoundException(sprintf('%s with id %s not found', self::ENTITY_NAME, $leaveRequestId));
        }

        $this->leaveRequestRepository->delete($leaveRequest);
    }
}