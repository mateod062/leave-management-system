<?php

namespace App\Controller;

use App\DTO\LeaveRequestDTO;
use App\DTO\LeaveRequestFilterDTO;
use App\Form\LeaveRequestCreationType;
use App\Service\Auth\Interface\AuthenticationServiceInterface;
use App\Service\Comment\Interface\CommentServiceInterface;
use App\Service\LeaveRequest\Interface\LeaveRequestPersistenceServiceInterface;
use App\Service\LeaveRequest\Interface\LeaveRequestQueryServiceInterface;
use App\Service\Notification\Interface\NotificationServiceInterface;
use App\Service\Team\Interface\TeamServiceInterface;
use App\Service\User\Interface\UserQueryServiceInterface;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
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


    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function adminDashboard(): Response
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

    #[Route('/project-manager/dashboard', name: 'project_manager_dashboard', methods: ['GET', 'POST'])]
    public function projectManagerDashboard(Request $request): Response
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

    #[Route('/team-lead/dashboard', name: 'team_lead_dashboard', methods: ['GET', 'POST'])]
    public function teamLeadDashboard(Request $request): Response
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

    #[Route('/employee/dashboard', name: 'employee_dashboard', methods: ['GET', 'POST'])]
    public function employeeDashboard(Request $request): Response
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

    #[Route('/leave-request/{id}', name: 'leave_request_details', methods: ['GET'])]
    public function leaveRequestDetails(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $leaveRequest = $this->leaveRequestQueryService->getLeaveRequestById($id);
        $comments = $this->commentService->getComments($id);

        foreach ($comments as $comment) {
            $comment['user'] = $this->userQueryService->getUserById($comment->getUserId());
            $comment['replies'] = $this->commentService->getReplies($comment->getId());
        }

        foreach ($comments as $comment) {
            foreach ($comment['replies'] as $reply) {
                $reply['user'] = $this->userQueryService->getUserById($reply->getUserId());
            }
        }

        $leaveRequest['user'] = $this->userQueryService->getUserById($leaveRequest->getUserId());

        return $this->render('employee/leave_request_details.html.twig', [
            'leave_request' => $leaveRequest,
            'comments' => $comments
            ]);
    }

    #[Route('/leave-history', name: 'leave_history', methods: ['GET'])]
    public function leaveHistory(Request $request): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $leaveRequests = $this->leaveRequestQueryService->getLeaveHistory($user->getId());
        return $this->render('employee/leave_history.html.twig', ['leave_requests' => $leaveRequests]);
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
}