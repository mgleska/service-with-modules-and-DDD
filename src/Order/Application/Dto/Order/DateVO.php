<?php

declare(strict_types=1);

namespace App\Order\Application\Dto\Order;

use DateTime;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use JsonSerializable;
use OpenApi\Attributes as OA;

use function is_string;

#[OA\Schema(type: 'string', format: 'date', example: '2024-10-15')]
class DateVO implements JsonSerializable
{
    private DateTimeImmutable $date;

    /**
     * @throws Exception
     */
    public function __construct(mixed $date)
    {
        if ($date instanceof DateTime) {
            $this->date = new DateTimeImmutable($date->format('Y-m-d'));
            return;
        }
        if (is_string($date)) {
            $this->date = new DateTimeImmutable($date);
            return;
        }
        throw new InvalidArgumentException('Constructor argument must be a string or DateTime');
    }

    public function __toString(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function jsonSerialize(): string
    {
        return $this->date->format('Y-m-d');
    }
}
