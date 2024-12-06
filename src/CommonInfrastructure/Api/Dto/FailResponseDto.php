<?php

declare(strict_types=1);

namespace App\CommonInfrastructure\Api\Dto;

use App\CommonInfrastructure\Api\ResponseStatusEnum;
use OpenApi\Attributes as OA;

class FailResponseDto
{
    // https://github.com/omniti-labs/jsend

    #[OA\Property(example: 'fail')]
    public string $status;

    #[OA\Property(type: 'object', example: '{"id": 1}')]
    public mixed $data;

    public function __construct(string $message)
    {
        $this->status = ResponseStatusEnum::FAIL->value;
        $this->data = ['message' => $message];
    }
}
