<?php

declare(strict_types=1);

namespace App\Order\Application\Enum;

enum ApiProblemTypeEnum: string
{
    case VALIDATOR = 'order/validator';
    case CREATE = 'order/create';
    case UPDATE_LINES = 'order/update-lines';
}
