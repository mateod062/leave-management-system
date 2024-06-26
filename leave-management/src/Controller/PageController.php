<?php

namespace App\Controller;

use App\DTO\CommentCreationDTO;
use App\DTO\LeaveRequestDTO;
use App\DTO\LeaveRequestFilterDTO;
use App\Entity\UserRole;
use App\Form\LeaveRequestCreationType;
use App\Form\PostCommentType;
use App\Service\Auth\Interface\AuthenticationServiceInterface;
use App\Service\Comment\Interface\CommentServiceInterface;
use App\Service\LeaveRequest\Interface\LeaveRequestPersistenceServiceInterface;
use App\Service\LeaveRequest\Interface\LeaveRequestQueryServiceInterface;
use App\Service\Notification\Interface\NotificationServiceInterface;
use App\Service\Team\Interface\TeamServiceInterface;
use App\Service\User\Interface\UserQueryServiceInterface;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly LeaveRequestQueryServiceInterface $leaveRequestQueryService,
        private readonly LeaveRequestPersistenceServiceInterface $leaveRequestPersistenceService,
        private readonly NotificationServiceInterface $notificationService,
        private readonly TeamServiceInterface $teamService,
        private readonly CommentServiceInterface $commentService,
        private readonly UserQueryServiceInterface $userQueryService
    ) {}

    #[Route('/home', name: 'home', methods: ['GET', 'POST', 'PUT', 'DELETE'])]
    public function dashboard(Request $request): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();

        return match ($user->getRole()) {
            UserRole::ROLE_ADMIN->value => $this->adminDashboard(),
            UserRole::ROLE_PROJECT_MANAGER->value => $this->projectManagerDashboard($request),
            UserRole::ROLE_TEAM_LEAD->value => $this->teamLeadDashboard($request),
            default => $this->employeeDashboard($request),
        };
    }

    private function adminDashboard(): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $users = $this->userQueryService->getUsers();
        $leaveRequests = $this->leaveRequestQueryService->getLeaveRequests(new LeaveRequestFilterDTO());

        $displayLeaveRequests = [];

        foreach ($leaveRequests as $leaveRequest) {
            $user = $this->userQueryService->getUserById($leaveRequest->getUserId());
            $displayLeaveRequests[] = [
                'request' => $leaveRequest,
                'user' => $user
            ];
        }

        return $this->render('admin/dashboard.html.twig', [
            'user' => $user,
            'users' => $users,
            'leave_requests' => $displayLeaveRequests
        ]);
    }

    private function projectManagerDashboard(Request $request): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $myLeaveRequests = $this->leaveRequestQueryService->getLeaveRequests(new LeaveRequestFilterDTO(
            userId: $user->getId()
        ));
        $leaveRequests = $this->leaveRequestQueryService->getLeaveRequestsForApprover();
        $teams = $this->teamService->getManagedTeams($user->getId());

        $displayLeaveRequests = [];

        foreach ($leaveRequests as $leaveRequest) {
            $user = $this->userQueryService->getUserById($leaveRequest->getUserId());
            $displayLeaveRequests[] = [
                'request' => $leaveRequest,
                'user' => $user
            ];
        }

        $form = $this->createForm(LeaveRequestCreationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $this->leaveRequestPersistenceService->createLeaveRequest(
                    new LeaveRequestDTO(
                        userId: $this->authenticationService->getAuthenticatedUser()->getId(),
                        startDate: $data->getStartDate(),
                        endDate: $data->getEndDate(),
                        reason: $data->getReason()
                    )
                );

                return $this->redirectToRoute('project_manager_dashboard');
            } catch (ReflectionException | ORMException $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            } catch (Exception $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->render('project_manager/dashboard.html.twig', [
            'user' => $user,
            'my_requests' => $myLeaveRequests,
            'leave_requests' => $displayLeaveRequests,
            'teams' => $teams,
            'form' => $form->createView()
            ]);
    }

    private function teamLeadDashboard(Request $request): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $leaveRequests = $this->leaveRequestQueryService->getLeaveRequestsForApprover();
        $myLeaveRequests = $this->leaveRequestQueryService->getLeaveRequests(new LeaveRequestFilterDTO(
            userId: $user->getId()
        ));
        $team = $this->teamService->getLeadingTeam($user->getId());

        $displayLeaveRequests = [];

        foreach ($leaveRequests as $leaveRequest) {
            $user = $this->userQueryService->getUserById($leaveRequest->getUserId());
            $displayLeaveRequests[] = [
                'request' => $leaveRequest,
                'user' => $user
            ];
        }

        $form = $this->createForm(LeaveRequestCreationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $this->leaveRequestPersistenceService->createLeaveRequest(
                    new LeaveRequestDTO(
                        userId: $this->authenticationService->getAuthenticatedUser()->getId(),
                        startDate: $data->getStartDate(),
                        endDate: $data->getEndDate(),
                        reason: $data->getReason()
                    )
                );

                return $this->redirectToRoute('team_lead_dashboard');
            } catch (ReflectionException | ORMException $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            } catch (Exception $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->render('team_lead/dashboard.html.twig', [
            'user' => $user,
            'leave_requests' => $displayLeaveRequests,
            'my_requests' => $myLeaveRequests,
            'team' => $team,
            'form' => $form->createView()
        ]);
    }

    private function employeeDashboard(Request $request): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $leaveRequests = $this->leaveRequestQueryService->getLeaveRequests(new LeaveRequestFilterDTO(
            userId: $user->getId()
        ));

        $form = $this->createForm(LeaveRequestCreationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $this->leaveRequestPersistenceService->createLeaveRequest(
                    new LeaveRequestDTO(
                        userId: $this->authenticationService->getAuthenticatedUser()->getId(),
                        startDate: $data->getStartDate(),
                        endDate: $data->getEndDate(),
                        reason: $data->getReason()
                    )
                );

                return $this->redirectToRoute('employee_dashboard');
            } catch (ReflectionException | ORMException $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            } catch (Exception $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->render('employee/dashboard.html.twig', [
            'user' => $user,
            'leave_requests' => $leaveRequests,
            'form' => $form->createView()
            ]);
    }

    #[Route('/leave-request/{id}', name: 'leave_request_details', methods: ['GET', 'POST'])]
    public function leaveRequestDetails(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $leaveRequest = $this->leaveRequestQueryService->getLeaveRequestById($id);
        $comments = $this->commentService->getComments($id);

        $displayComments = [];

        foreach ($comments as $comment) {
            $displayComments[] = $this->formatCommentWithReplies($comment);
        }

        $displayLeaveRequest = [
            'request' => $leaveRequest,
            'user' => $this->userQueryService->getUserById($leaveRequest->getUserId())
        ];

        $form = $this->createForm(PostCommentType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->json($form->getErrors(), Response::HTTP_BAD_REQUEST);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $this->commentService->addComment(new CommentCreationDTO(
                    userId: $this->authenticationService->getAuthenticatedUser()->getId(),
                    leaveRequestId: $id,
                    parentCommentId: $data->getParentCommentId(),
                    message: $data->getMessage()
                ));
            } catch (EntityNotFoundException $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            } catch (Exception $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->render('leave_request/details.html.twig', [
            'leave_request' => $displayLeaveRequest,
            'comments' => $displayComments,
            'form' => $form->createView()
        ]);
    }

    #[Route('/leave-history', name: 'leave_history', methods: ['GET'])]
    public function leaveHistory(Request $request): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $leaveRequests = $this->leaveRequestQueryService->getLeaveHistory($user->getId());
        return $this->render('leave_history/index.html.twig', ['leave_requests' => $leaveRequests]);
    }

    #[Route('/notifications', name: 'notifications', methods: ['GET'])]
    public function notifications(): Response
    {
        $notifications = $this->notificationService->getUserNotifications();
        return $this->render('notifications/index.html.twig', ['notifications' => $notifications]);
    }

    #[Route('/team-calendar/{id}', name: 'team_calendar', methods: ['GET'])]
    public function teamCalendar(Request $request): Response
    {
        $teamId = $request->attributes->get('id');
        $now = new DateTime();
        $month = $now->format('m');
        $year = $now->format('Y');
        $calendar = $this->leaveRequestQueryService->getLeaveRequestsForTeamCalendar($teamId, $month, $year);
        return $this->render('team_calendar/index.html.twig', ['calendar' => $calendar]);
    }

    /**
     * Helper method to format a comment with its replies recursively
     *
     * @param $comment
     * @return array
     */
    private function formatCommentWithReplies($comment): array
    {
        $displayReplies = [];
        $replies = $this->commentService->getReplies($comment->getId());

        foreach ($replies as $reply) {
            $displayReplies[] = $this->formatCommentWithReplies($reply);
        }

        return [
            'comment' => $comment,
            'user' => $this->userQueryService->getUserById($comment->getUserId()),
            'replies' => $displayReplies
        ];
    }
}