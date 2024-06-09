<?php

namespace App\Service\Auth;

use App\Service\Auth\Interface\AuthorizationServiceInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function isGranted(string $role): bool
    {
        return $this->authorizationChecker->isGranted($role);
    }

    public function denyAccessUnlessGranted(string $role): void
    {
        if (!$this->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }
    }
}