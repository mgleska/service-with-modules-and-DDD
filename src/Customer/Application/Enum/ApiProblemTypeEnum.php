<?php

declare(strict_types=1);

namespace App\Customer\Application\Enum;

enum ApiProblemTypeEnum: string
{
    case VALIDATOR = 'customer/validator';
}
