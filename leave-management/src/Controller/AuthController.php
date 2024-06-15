<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Auth\AuthenticationService;
use App\Service\Auth\Interface\AuthenticationServiceInterface;
use Exception;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AuthController extends AbstractController
{
    private function __construct(
        private readonly AuthenticationServiceInterface $authenticationService
    ) {}

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($user);
    }

    #[Route('/me', name: 'authenticated_user', methods: ['GET'])]
    public function me(): JsonResponse {
        try {
            return $this->json($this->authenticationService->getAuthenticatedUser());
        } catch (ReflectionException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (AuthenticationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout() {}
}