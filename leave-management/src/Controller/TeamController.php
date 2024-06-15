<?php

namespace App\Controller;

use App\DTO\TeamCreationDTO;
use App\Service\Team\Interface\TeamServiceInterface;
use App\Service\Team\TeamService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use LogicException;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    public function __construct(
        private readonly TeamServiceInterface $teamService
    )
    {}

    #[Route('/teams/{teamId}', name: 'get_team_members', methods: ['GET'])]
    public function getTeamsMembers(Request $request): JsonResponse
    {
        try {
            return $this->json($this->teamService->getTeamMembers($request->attributes->get('teamId')));
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/teams', name: 'create_team', methods: ['POST'])]
    public function createTeam(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $team = new TeamCreationDTO(
            name: $data['name'],
            members: $data['members'],
            teamLeadId: $data['teamLeadId'],
            projectManagerId: $data['projectManagerId']
        );

        try {
            return $this->json($this->teamService->createTeam($team));
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (ORMException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/teams/{teamId}/add-user/{userId}', name: 'add_user_to_team', methods: ['PUT'])]
    public function addUserToTeam(Request $request): JsonResponse
    {
        try {
            return $this->json(
                $this->teamService->addUserToTeam($request->attributes->get('teamId'), $request->attributes->get('userId'))
            );
        } catch (ReflectionException|LogicException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/teams/{teamId}/remove-user/{userId}', name: 'remove_user_from_team', methods: ['PUT'])]
    public function removeUserFromTeam(Request $request): JsonResponse
    {
        try {
            return $this->json(
                $this->teamService->removeUserFromTeam($request->attributes->get('teamId'), $request->attributes->get('userId'))
            );
        } catch (ReflectionException|LogicException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/teams/{teamId}/assign-team-lead/{userId}', name: 'assign_team_lead', methods: ['PUT'])]
    public function assignTeamLead(Request $request): JsonResponse
    {
        try {
            return $this->json(
                $this->teamService->assignTeamLead($request->attributes->get('teamId'), $request->attributes->get('userId'))
            );
        } catch (ReflectionException|LogicException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/teams/{teamId}/assign-project-manager/{userId}', name: 'assign_project_manager', methods: ['PUT'])]
    public function assignProjectManager(Request $request): JsonResponse
    {
        try {
            return $this->json(
                $this->teamService->assignProjectManager($request->attributes->get('teamId'), $request->attributes->get('userId'))
            );
        } catch (ReflectionException|LogicException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/teams/{teamId}', name: 'delete_team', methods: ['DELETE'])]
    public function deleteTeam(Request $request): JsonResponse
    {
        try {
            $this->teamService->deleteTeam($request->attributes->get('teamId'));
            return $this->json(['message' => 'Team deleted successfully']);
        } catch (LogicException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}