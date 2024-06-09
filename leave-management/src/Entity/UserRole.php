<?php

namespace App\Entity;

enum UserRole: string
{
    case ROLE_EMPLOYEE = 'ROLE_EMPLOYEE';
    case ROLE_TEAM_LEAD = 'ROLE_TEAM_LEAD';
    case ROLE_PROJECT_MANAGER = 'ROLE_PROJECT_MANAGER';
    case ROLE_ADMIN = 'ROLE_ADMIN';
}