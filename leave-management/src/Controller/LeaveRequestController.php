<?php

namespace App\Controller;

use App\DTO\LeaveRequestDTO;
use App\DTO\LeaveRequestFilterDTO;
use App\Service\Auth\AuthenticationService;
use App\Service\Auth\AuthorizationService;
use App\Service\LeaveRequest\LeaveRequestPersistenceService;
use App\Service\LeaveRequest\LeaveRequestQueryService;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LeaveRequestController extends AbstractController
{
    public function __construct(
        private LeaveRequestPersistenceService $leaveRequestPersistenceService,
        private LeaveRequestQueryService $leaveRequestQueryService,
        private AuthenticationService $authenticationService,
        private AuthorizationService $authorizationService,
    ) {}

    #[Route('/leave-requests', name: 'get_all_leave_requests', methods: ['GET'])]
    public function getAllLeaveRequests(): JsonResponse
    {
        try {
            return $this->json($this->leaveRequestQueryService->getLeaveRequests(new LeaveRequestFilterDTO()));
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/leave-requests/me', name: 'get_own_leave_requests', methods: ['GET'])]
    public function getOwnLeaveRequests(): JsonResponse
    {
        try {
            $user = $this->authenticationService->getAuthenticatedUser();

            $leaveRequests = $this->leaveRequestQueryService->getLeaveRequests(
                new LeaveRequestFilterDTO($user->getId())
            );

            return $this->json($leaveRequests);
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (AuthenticationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/leave-requests/{userId}', name: 'get_user_leave_requests', methods: ['GET'])]
    public function getUserLeaveRequests(Request $request): JsonResponse
    {
        try {
            $leaveRequests = $this->leaveRequestQueryService->getLeaveRequests(
                new LeaveRequestFilterDTO(userId: (int) $request->attributes->get('userId'))
            );

            return $this->json($leaveRequests);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/leave-requests/team/{teamId}', name: 'get_team_calendar_of_leave_requests', methods: ['GET'])]
    public function getTeamCalendar(Request $request): JsonResponse
    {
        try {
            $this->authorizationService->denyAccessUnlessMemberOfTeam($request->attributes->get('teamId'));

            $teamId = (int) $request->attributes->get('teamId');
            $month = (int) $request->query->get('month');
            $year = (int) $request->query->get('year');

            return $this->json($this->leaveRequestQueryService->getLeaveRequestsForTeamCalendar($teamId, $month, $year));
        } catch (ORMException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createLeaveRequest(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $user = $this->authenticationService->getAuthenticatedUser();
            $leaveRequest = $this->leaveRequestPersistenceService->createLeaveRequest(
                new LeaveRequestDTO(
                    userId: $user->getId(),
                    startDate: new DateTime($data['startDate']),
                    endDate: new DateTime($data['endDate']),
                    reason: $data['reason']
                )
            );

            return $this->json($leaveRequest);
        } catch (ReflectionException | ORMException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/leave-requests/approve/{id}', name: 'approve_leave_request', methods: ['PUT'])]
    public function approveLeaveRequest(Request $request): JsonResponse
    {
        try {
            $leaveRequestId = $this->leaveRequestQueryService->getLeaveRequests(
                new LeaveRequestFilterDTO(id: (int) $request->attributes->get('id'))
            )[0];

            $this->leaveRequestPersistenceService->approveLeaveRequest($leaveRequestId);

            return $this->json($leaveRequestId);
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (AuthenticationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/leave-requests/reject/{id}', name: 'reject_leave_request', methods: ['PUT'])]
    public function rejectLeaveRequest(Request $request): JsonResponse
    {
        try {
            $leaveRequestId = $this->leaveRequestQueryService->getLeaveRequests(
                new LeaveRequestFilterDTO(id: (int) $request->attributes->get('id'))
            )[0];

            $this->leaveRequestPersistenceService->rejectLeaveRequest($leaveRequestId);

            return $this->json($leaveRequestId);
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (AuthenticationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/leave-requests/{id}', name: 'delete_leave_request', methods: ['DELETE'])]
    public function deleteLeaveRequest(Request $request): JsonResponse
    {
        try {
            $leaveRequestId = $this->leaveRequestQueryService->getLeaveRequests(
                new LeaveRequestFilterDTO(id: (int) $request->attributes->get('id'))
            )[0];

            $this->leaveRequestPersistenceService->deleteLeaveRequest($leaveRequestId);

            return $this->json($leaveRequestId);
        } catch (AuthenticationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}