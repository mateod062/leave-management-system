<?php

namespace App\Constraint;

use App\Service\Auth\Interface\AuthenticationServiceInterface;
use Carbon\Carbon;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidLeaveRequestDatesValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authenticationService
    )
    {}


    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value || !$value->getStartDate() || !$value->getEndDate()) {
            return;
        }

        $startDate = $value->getStartDate();
        $endDate = $value->getEndDate();

        $start = Carbon::instance($startDate);
        $end = Carbon::instance($endDate);

        $daysRequested = $start->diffInWeekdays($end) + 1;

        $user = $this->authenticationService->getAuthenticatedUser();
        $userLeaveBalance = $user->getLeaveBalance();

        if ($daysRequested > $userLeaveBalance) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}