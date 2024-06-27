<?php

namespace App\Security\Voter;


use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class UserVoter extends Voter implements VoterInterface
{
    const VIEW = 'VIEW';
    const ADD = 'ADD';
    const EDIT = 'EDIT';
    const DELETE = 'DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::ADD, self::EDIT, self::DELETE]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::ADD => $this->canAdd($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(User $subject, User $user): bool
    {
        $role = $user->getRole();
        return in_array($role, ['ROLE_ADMIN', 'ROLE_PROJECT_MANAGER', 'ROLE_TEAM_LEAD']);
    }

    private function canAdd(User $subject, User $user): bool
    {
        return $user->getRole() === 'ROLE_ADMIN';
    }

    private function canEdit(User $subject, User $user): bool
    {
        return $user->getRole() === 'ROLE_ADMIN';
    }

    private function canDelete(User $subject, User $user): bool
    {
        return $user->getRole() === 'ROLE_ADMIN';
    }
}