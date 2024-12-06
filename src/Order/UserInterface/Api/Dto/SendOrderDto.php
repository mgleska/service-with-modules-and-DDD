<?php

declare(strict_types=1);

namespace App\Order\UserInterface\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SendOrderDto
{
    #[Assert\Range(min: 1)]
    public readonly int $orderId;

    #[Assert\Range(min: 1)]
    public readonly int $version;

    public function __construct(
        int $orderId,
        int $version,
    ) {
        $this->orderId = $orderId;
        $this->version = $version;
    }
}
