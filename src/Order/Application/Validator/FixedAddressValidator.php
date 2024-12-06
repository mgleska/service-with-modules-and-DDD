<?php

declare(strict_types=1);

namespace App\Order\Application\Validator;

use App\CommonInfrastructure\Api\ApiProblemException;
use App\Order\Domain\FixedAddress;
use App\Order\Application\Enum\ApiProblemTypeEnum;
use App\Order\Infrastructure\Repository\FixedAddressRepository;
use Symfony\Component\HttpFoundation\Response;

class FixedAddressValidator
{
    public function __construct(
        private readonly FixedAddressRepository $addressRepository,
    ) {
    }

    public function validateExists(?FixedAddress $address): void
    {
        if ($address === null) {
            throw new ApiProblemException(
                Response::HTTP_NOT_FOUND,
                ApiProblemTypeEnum::VALIDATOR->value,
                'ORDER_FIXEDADDRESS_NOT_FOUND'
            );
        }
    }

    public function validateExternalIdNotUsed(string $externalId): void
    {
        $address = $this->addressRepository->findOneBy(['externalId' => $externalId]);
        if ($address !== null) {
            throw new ApiProblemException(
                Response::HTTP_PRECONDITION_FAILED,
                ApiProblemTypeEnum::VALIDATOR->value,
                'ORDER_FIXEDADDRESS_EXTERNAL_ID_ALREADY_EXIST'
            );
        }
    }
}
