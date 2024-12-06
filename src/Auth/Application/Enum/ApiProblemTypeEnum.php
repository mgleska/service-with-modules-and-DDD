<?php

declare(strict_types=1);

namespace App\Auth\Application\Enum;

enum ApiProblemTypeEnum: string
{
    case VALIDATOR = 'auth/validator';
}
