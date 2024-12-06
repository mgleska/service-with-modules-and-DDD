<?php

declare(strict_types=1);

namespace App\Order\Application\Dto\Order;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class OrderAddressContactDto
{
    #[Assert\Length(min: 3, max: 250)]
    #[Assert\Regex('/^\S/')]
    #[OA\Property(example: 'John Doe')]
    public readonly string $contactPerson;

    #[Assert\Length(min: 9, max: 250)]
    #[Assert\Regex('/^([+]48-?)?[0-9]{3}[-]?[0-9]{3}[-]?[0-9]{3}$/')]
    #[OA\Property(example: '+48-123-456-789')]
    public readonly string $contactPhone;

    #[Assert\Length(min: 1, max: 250)]
    #[Assert\Email]
    #[OA\Property(example: 'johh.doe@acme.com')]
    public readonly ?string $contactEmail;

    public function __construct(
        string $contactPerson,
        string $contactPhone,
        ?string $contactEmail,
    ) {
        $this->contactPerson = $contactPerson;
        $this->contactPhone = $contactPhone;
        $this->contactEmail = $contactEmail;
    }
}
