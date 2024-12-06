<?php

declare(strict_types=1);

namespace App\Order\Application\Dto\Order;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class OrderAddressDto
{
    #[Assert\Length(min: 1, max: 250)]
    #[OA\Property(example: 'Acme Company Warehouse 1')]
    public readonly string $nameCompanyOrPerson;

    #[Assert\Length(min: 1, max: 250)]
    #[OA\Property(example: 'ul. Garbary 125')]
    public readonly string $address;

    #[Assert\Length(min: 1, max: 250)]
    #[OA\Property(example: 'Poznań')]
    public readonly string $city;

    #[Assert\Length(min: 1, max: 50)]
    #[OA\Property(example: '61-719')]
    public readonly string $zipCode;

    public function __construct(
        string $nameCompanyOrPerson,
        string $address,
        string $city,
        string $zipCode,
    ) {
        $this->nameCompanyOrPerson = $nameCompanyOrPerson;
        $this->address = $address;
        $this->city = $city;
        $this->zipCode = $zipCode;
    }
}
