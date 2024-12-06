<?php

declare(strict_types=1);

namespace App\CommonInfrastructure\Api\Dto;

use App\CommonInfrastructure\Api\ResponseStatusEnum;
use OpenApi\Attributes as OA;

class SuccessResponseDto
{
    // https://github.com/omniti-labs/jsend

    #[OA\Property(example: 'success')]
    public string $status;

    #[OA\Property(type: 'object', example: '{"id": 1}')]
    public mixed $data;

    public function __construct(mixed $data = null)
    {
        $this->status = ResponseStatusEnum::SUCCESS->value;
        $this->data = $data;
    }
}
