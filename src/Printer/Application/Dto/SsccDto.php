<?php

declare(strict_types=1);

namespace App\Printer\Application\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SsccDto
{
    #[Assert\Length(exactly: 18)]
    #[Assert\NotNull]
    public readonly string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }
}
