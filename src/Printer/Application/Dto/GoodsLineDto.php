<?php

declare(strict_types=1);

namespace App\Printer\Application\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class GoodsLineDto
{
    #[Assert\Length(min:1, max:25)]
    #[Assert\NotNull]
    public readonly string $description;

    #[Assert\Range(min: 1, max: 99)]
    #[Assert\NotNull]
    public readonly int $quantity;

    public function __construct(string $description, int $quantity)
    {
        $this->description = $description;
        $this->quantity = $quantity;
    }
}
