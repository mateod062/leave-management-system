<?php

namespace App\Entity;

enum UserRole: string
{
    case EMPLOYEE = 'employee';
    case TEAM_LEAD = 'team_lead';
    case PROJECT_MANAGER = 'project_manager';
    case ADMIN = 'admin';
}