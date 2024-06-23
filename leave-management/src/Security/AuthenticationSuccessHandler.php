<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $role = $token->getRoleNames();

        $targetPath = $this->getDefaultTargetPath($role[0]);

        return new RedirectResponse($targetPath);
    }

    private function getDefaultTargetPath(string $role): string
    {
        return match ($role) {
            'ROLE_ADMIN' => '/admin/dashboard',
            'ROLE_EMPLOYEE' => '/employee/dashboard',
            'ROLE_PROJECT_MANAGER' => '/project-manager/dashboard',
            'ROLE_TEAM_LEAD' => '/team-lead/dashboard',
            default => '/',
        };
    }
}