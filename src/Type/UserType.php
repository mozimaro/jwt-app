<?php

namespace App\Type;

enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case VISITOR = 'visitor';
}
