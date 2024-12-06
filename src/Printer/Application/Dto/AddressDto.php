<?php

declare(strict_types=1);

namespace App\Printer\Application\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AddressDto
{
    #[Assert\Length(min:1, max:40)]
    #[Assert\NotNull]
    public readonly string $line1;

    #[Assert\Length(min:1, max:40)]
    #[Assert\NotNull]
    public readonly string $line2;

    #[Assert\Length(min:1, max:15)]
    #[Assert\NotNull]
    public readonly string $zipCode;

    #[Assert\Length(min:1, max:25)]
    #[Assert\NotNull]
    public readonly string $city;

    public function __construct(string $line1, string $line2, string $zipCode, string $city)
    {
        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->zipCode = $zipCode;
        $this->city = $city;
    }
}
