<?php

namespace App\Controller;

use App\DTO\UserCreationDTO;
use App\DTO\UserDTO;
use App\Entity\UserRole;
use App\Service\Team\TeamService;
use App\Service\User\UserPersistenceService;
use App\Service\User\UserQueryService;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserQueryService $userQueryService,
        private readonly UserPersistenceService $userPersistenceService,
        private readonly TeamService $teamService
    ) {}

    #[Route('/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        try {
            return $this->json($this->userQueryService->getUsers());
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/users/team/{teamId}', name: 'get_team_members', methods: ['GET'])]
    public function getTeamMembers(Request $request): JsonResponse
    {
        try {
            return $this->json($this->teamService->getTeamMembers($request->attributes->get('teamId')));
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        try {
            return $this->json($this->userQueryService->getUserById($id));
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/users/email/{email}', name: 'get_user_by_email', methods: ['GET'])]
    public function getUserByEmail(string $email): JsonResponse
    {
        try {
            return $this->json($this->userQueryService->getUserByEmail($email));
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (EntityNotFoundException $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new UserCreationDTO($data['username'], $data['email'], $data['password']);

        try {
            return match ($data['role']) {
                UserRole::ROLE_EMPLOYEE->value => $this->json($this->userPersistenceService->createEmployee($user)),
                UserRole::ROLE_TEAM_LEAD->value => $this->json($this->userPersistenceService->createTeamLead($user)),
                UserRole::ROLE_PROJECT_MANAGER->value => $this->json($this->userPersistenceService->createProjectManager($user)),
                UserRole::ROLE_ADMIN->value => $this->json($this->userPersistenceService->createAdmin($user)),
                default => $this->json(['error' => 'Invalid role'], Response::HTTP_BAD_REQUEST)
            };
        } catch (ORMException | OptimisticLockException | ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $request->attributes->get('id');

        $userDTO = new UserDTO($userId, $data['username'], $data['email'], $data['password'], $data['role']);

        try {
            return $this->json($this->userPersistenceService->updateUser($data['id'], $userDTO));
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(Request $request): JsonResponse
    {
        try {
            $this->userPersistenceService->deleteUser($request->attributes->get('id'));
            return $this->json(['message' => 'User deleted successfully']);
        } catch (EntityNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}