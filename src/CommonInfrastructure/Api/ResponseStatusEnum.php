<?php

declare(strict_types=1);

namespace App\CommonInfrastructure\Api;

enum ResponseStatusEnum: string
{
    // https://github.com/omniti-labs/jsend

    case SUCCESS = 'success';
    case FAIL = 'fail';
}
