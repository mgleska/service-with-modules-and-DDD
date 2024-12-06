<?php

declare(strict_types=1);

namespace App\Order\Application\Query;

use App\Order\Application\Dto\FixedAddress\FixedAddressDto;
use App\Order\Application\Validator\FixedAddressValidator;
use App\Order\Infrastructure\Repository\FixedAddressRepository;

class FixedAddressQuery
{
    public function __construct(
        private readonly FixedAddressRepository $addressRepository,
        private readonly FixedAddressValidator $addressValidator,
    ) {
    }

    public function getFixedAddress(int $id): FixedAddressDto
    {
        $address = $this->addressRepository->find($id);
        $this->addressValidator->validateExists($address);

        return FixedAddressDto::fromEntity($address);
    }

    /**
     * @return FixedAddressDto[]
     */
    public function getAllFixedAddresses(): array
    {
        $addresses = $this->addressRepository->findAll();

        $result = [];
        foreach ($addresses as $address) {
            $result[$address->getId()] = FixedAddressDto::fromEntity($address);
        }

        return $result;
    }
}
