<?php

declare(strict_types=1);

namespace App\Order\Application\Dto\FixedAddress;

use App\Order\Domain\FixedAddress;
use OpenApi\Attributes as OA;

class FixedAddressDto
{
    public readonly int $id;
    public readonly int $version;

    #[OA\Property(minLength: 1, maxLength: 100, example: 'WH1')]
    public readonly string $externalId;

    #[OA\Property(minLength: 1, maxLength: 250, example: 'Acme Company Warehouse 1')]
    public readonly string $nameCompanyOrPerson;

    #[OA\Property(minLength: 1, maxLength: 250, example: 'ul. Garbary 125')]
    public readonly string $address;

    #[OA\Property(minLength: 1, maxLength:250, example: 'Poznań')]
    public readonly string $city;

    #[OA\Property(minLength: 1, maxLength:250, example: '61-719')]
    public readonly string $zipCode;

    public function __construct(int $id, int $version, string $externalId, string $nameCompanyOrPerson, string $address, string $city, string $zipCode)
    {
        $this->id = $id;
        $this->version = $version;
        $this->externalId = $externalId;
        $this->nameCompanyOrPerson = $nameCompanyOrPerson;
        $this->address = $address;
        $this->city = $city;
        $this->zipCode = $zipCode;
    }

    public static function fromEntity(FixedAddress $address): self
    {
        return new self(
            $address->getId(),
            $address->getVersion(),
            $address->getExternalId(),
            $address->getNameCompanyOrPerson(),
            $address->getAddress(),
            $address->getCity(),
            $address->getZipCode()
        );
    }
}
