<?php

namespace App\Controller;

use App\DTO\LeaveRequestFilterDTO;
use App\Service\Auth\Interface\AuthenticationServiceInterface;
use App\Service\Comment\Interface\CommentServiceInterface;
use App\Service\LeaveRequest\Interface\LeaveRequestQueryServiceInterface;
use App\Service\Notification\Interface\NotificationServiceInterface;
use App\Service\User\Interface\UserQueryServiceInterface;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly LeaveRequestQueryServiceInterface $leaveRequestQueryService,
        private readonly NotificationServiceInterface $notificationService,
        private readonly CommentServiceInterface $commentService,
        private readonly UserQueryServiceInterface $userQueryService
    ) {}


    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function adminDashboard(): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $leaveRequests = $this->leaveRequestQueryService->getLeaveRequests(new LeaveRequestFilterDTO());

        foreach ($leaveRequests as $leaveRequest) {
            $leaveRequest['user'] = $this->userQueryService->getUserById($leaveRequest->getUserId());
        }

        return $this->render('admin/dashboard.html.twig', [
            'user' => $user,
            'leave_requests' => $leaveRequests
        ]);
    }

    #[Route('/project-manager/dashboard', name: 'project_manager_dashboard', methods: ['GET'])]
    public function projectManagerDashboard(): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $leaveRequests = $this->leaveRequestQueryService->getLeaveRequests(new LeaveRequestFilterDTO(
            userId: $user->getId()
        ));

        foreach ($leaveRequests as $leaveRequest) {
            $leaveRequest['user'] = $this->userQueryService->getUserById($leaveRequest->getUserId());
        }

        return $this->render('project_manager/dashboard.html.twig', [
            'user' => $user,
            'leave_requests' => $leaveRequests
            ]);
    }

    #[Route('/team-lead/dashboard', name: 'team_lead_dashboard', methods: ['GET'])]
    public function teamLeadDashboard(): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $leaveRequests = $this->leaveRequestQueryService->getLeaveRequests(new LeaveRequestFilterDTO());

        foreach ($leaveRequests as $leaveRequest) {
            $leaveRequest['user'] = $this->userQueryService->getUserById($leaveRequest->getUserId());
        }

        return $this->render('team_lead/dashboard.html.twig', [
            'user' => $user,
            'leave_requests' => $leaveRequests
        ]);
    }

    #[Route('/employee/dashboard', name: 'employee_dashboard', methods: ['GET'])]
    public function employeeDashboard(): Response
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $leaveRequests = $this->leaveRequestQueryService->getLeaveRequests(new LeaveRequestFilterDTO(
            userId: $user->getId()
        ));

        foreach ($leaveRequests as $leaveRequest) {
            $leaveRequest['user'] = $this->userQueryService->getUserById($leaveRequest->getUserId());
        }

        return $this->render('employee/dashboard.html.twig', [
            'user' => $user,
            'leave_requests' => $leaveRequests
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