<?php

namespace App\Enums;

enum UserGroupEnum: int
{
    case INDIVIDUAL = 1;
    case ENTITY = 2;
    case FEDERATION = 3;
    case ADMIN = 5;
}
