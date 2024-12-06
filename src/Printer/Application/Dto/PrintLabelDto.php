<?php

declare(strict_types=1);

namespace App\Printer\Application\Dto;

use App\CommonInfrastructure\GenericDtoValidator;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Callback([GenericDtoValidator::class, 'registerValidation'])]
class PrintLabelDto
{
    #[Assert\Valid]
    #[Assert\NotNull]
    public readonly AddressDto $loadingAddress;

    #[Assert\Valid]
    #[Assert\NotNull]
    public readonly AddressDto $deliveryAddress;

    /** @var GoodsLineDto[] $lines */
    #[Assert\Valid]
    #[Assert\NotNull]
    public readonly array $lines;

    /** @var SsccDto[] $ssccs */
    #[Assert\Valid]
    #[Assert\NotNull]
    public readonly array $ssccs;

    /**
     * @param GoodsLineDto[] $lines
     * @param SsccDto[] $ssccs
     */
    public function __construct(AddressDto $loadingAddress, AddressDto $deliveryAddress, array $lines, array $ssccs)
    {
        $this->loadingAddress = $loadingAddress;
        $this->deliveryAddress = $deliveryAddress;
        $this->lines = $lines;
        $this->ssccs = $ssccs;
    }
}
