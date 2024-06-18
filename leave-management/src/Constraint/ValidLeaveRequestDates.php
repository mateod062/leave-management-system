<?php

namespace App\Constraint;

use Symfony\Component\Validator\Constraint;

class ValidLeaveRequestDates extends Constraint
{
    public string $message = "The requested leave duration exceeds your available leave balance.";
}