<?php

declare(strict_types=1);

namespace App\Order\Application\Dto\Order;

use App\CommonInfrastructure\GenericDtoValidator;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Callback([GenericDtoValidator::class, 'registerValidation'])]
class UpdateOrderLinesDto
{
    #[Assert\Range(min: 1)]
    public readonly int $orderId;

    #[Assert\Range(min: 1)]
    public readonly int $version;

    /**
     * @var OrderLineDto[]
     */
    #[Assert\Valid]
    public readonly array $lines;

    /**
     * @param OrderLineDto[] $lines
     */
    public function __construct(int $orderId, int $version, array $lines)
    {
        $this->orderId = $orderId;
        $this->version = $version;
        $this->lines = $lines;
    }
}
