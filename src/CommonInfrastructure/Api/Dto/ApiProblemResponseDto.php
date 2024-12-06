<?php

declare(strict_types=1);

namespace App\CommonInfrastructure\Api\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\ConstraintViolation;

class ApiProblemResponseDto
{
    #[OA\Property(example: 'https://symfony.com/errors/validation')]
    #[OA\Property(example: 'order/validator')]
    public string $type;

    #[OA\Property(example: 'Validation Failed')]
    public string $title;

    #[OA\Property(example: '422')]
    public string $status;

    #[OA\Property(example: 'city: This value should be of type string.')]
    public ?string $detail;

    /**
     * @var ConstraintViolation[]|null
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(type: 'object'),
        example: '{"propertyPath": "city", "title": "This value should be of type string."}'
    )]
    public ?array $violations;
}
