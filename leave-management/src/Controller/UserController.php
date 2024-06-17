<?php

namespace App\Controller;

use App\DTO\UserCreationDTO;
use App\DTO\UserDTO;
use App\Entity\UserRole;
use App\Form\UserType;
use App\Service\Auth\AuthenticationService;
use App\Service\Auth\Interface\AuthenticationServiceInterface;
use App\Service\Mapper\MapperService;
use App\Service\Team\Interface\TeamServiceInterface;
use App\Service\User\Interface\UserPersistenceServiceInterface;
use App\Service\User\Interface\UserQueryServiceInterface;
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
        private readonly UserQueryServiceInterface $userQueryService,
        private readonly UserPersistenceServiceInterface $userPersistenceService,
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly MapperService $mapperService,
        private readonly TeamServiceInterface $teamService
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
    public function createUser(Request $request): Response
    {
        $form = $this->createForm(UserType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->json($form->getErrors(true), Response::HTTP_BAD_REQUEST);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = new UserCreationDTO($data['username'], $data['email'], $data['password']);

            try {
                switch ($data['role']) {
                    case UserRole::ROLE_EMPLOYEE->value:
                        $this->json($this->userPersistenceService->createEmployee($user));
                        break;
                    case UserRole::ROLE_TEAM_LEAD->value:
                        $this->json($this->userPersistenceService->createTeamLead($user));
                        break;
                    case UserRole::ROLE_PROJECT_MANAGER->value:
                        $this->json($this->userPersistenceService->createProjectManager($user));
                        break;
                    case UserRole::ROLE_ADMIN->value:
                        $this->json($this->userPersistenceService->createAdmin($user));
                        break;
                    default:
                        $this->json(['error' => 'Invalid role'], Response::HTTP_BAD_REQUEST);
                }

                return $this->redirectToRoute('admin/dashboard.html.twig',[
                    'id' => $this->authenticationService->getAuthenticatedUser()->getId()
                ]);
            } catch (Exception $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->render('admin/add_user.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(Request $request): Response
    {
        $userId = $request->attributes->get('id');
        $user = $this->userQueryService->getUserById($userId);

        $form = $this->createForm(UserType::class, [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'team' => $this->mapperService->mapToEntity($user)->getTeam()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->json($form->getErrors(true), Response::HTTP_BAD_REQUEST);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $userDTO = new UserDTO($userId, $data['username'], $data['email'], $data['password'], $data['role']);

            try {
                $this->json($this->userPersistenceService->updateUser($data['id'], $userDTO));

                return $this->redirectToRoute('admin/dashboard.html.twig',[
                    'id' => $this->authenticationService->getAuthenticatedUser()->getId()
                ]);
            } catch (EntityNotFoundException $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            } catch (ReflectionException $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            } catch (Exception $e) {
                return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->render('admin/edit_user.html.twig', ['form' => $form->createView()]);
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