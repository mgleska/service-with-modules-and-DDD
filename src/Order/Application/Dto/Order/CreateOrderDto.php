<?php

declare(strict_types=1);

namespace App\Order\Application\Dto\Order;

use App\CommonInfrastructure\GenericDtoValidator;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Callback([GenericDtoValidator::class, 'registerValidation'])]
class CreateOrderDto
{
    #[Assert\Date]
    public readonly string $loadingDate;

    #[Assert\Length(min: 1, max: 100)]
    #[OA\Property(example: 'WH1')]
    public readonly ?string $loadingFixedAddressExternalId;

    #[Assert\Valid]
    public readonly ?OrderAddressDto $loadingAddress;

    #[Assert\Valid]
    public readonly \App\Order\Application\Dto\Order\OrderAddressContactDto $loadingContact;

    #[Assert\Valid]
    public readonly OrderAddressDto $deliveryAddress;

    #[Assert\Valid]
    public readonly OrderAddressContactDto $deliveryContact;

    /**
     * @var OrderLineDto[]
     */
    #[Assert\Valid]
    public readonly array $lines;

    /**
     * @param OrderLineDto[] $lines
     */
    public function __construct(
        string $loadingDate,
        ?string $loadingFixedAddressExternalId,
        ?OrderAddressDto $loadingAddress,
        \App\Order\Application\Dto\Order\OrderAddressContactDto $loadingContact,
        OrderAddressDto $deliveryAddress,
        \App\Order\Application\Dto\Order\OrderAddressContactDto $deliveryContact,
        array $lines,
    ) {
        $this->loadingDate = $loadingDate;
        $this->loadingFixedAddressExternalId = $loadingFixedAddressExternalId;
        $this->loadingAddress = $loadingAddress;
        $this->loadingContact = $loadingContact;
        $this->deliveryAddress = $deliveryAddress;
        $this->deliveryContact = $deliveryContact;
        $this->lines = $lines;
    }
}
