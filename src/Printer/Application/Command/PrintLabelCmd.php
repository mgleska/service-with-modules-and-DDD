<?php

declare(strict_types=1);

namespace App\Printer\Application\Command;

use App\CommonInfrastructure\GenericDtoValidator;
use App\Printer\Application\Dto\PrintLabelDto;

use function sprintf;
use function str_repeat;

class PrintLabelCmd
{
    public function __construct(
        private readonly GenericDtoValidator $dtoValidator,
    ) {
    }

    public function printLabel(PrintLabelDto $dto): string
    {
        $this->dtoValidator->validate($dto, __FUNCTION__);

        $label = sprintf("%-40s | %-40s\n", 'Loading Address', 'Delivery Address');
        $label .= str_repeat('-', 81) . "\n";
        $label .= sprintf("%-40s | %-40s\n", $dto->loadingAddress->line1, $dto->deliveryAddress->line1);
        $label .= sprintf("%-40s | %-40s\n", $dto->loadingAddress->line2, $dto->deliveryAddress->line2);
        $label .= sprintf(
            "%-15s %-25s | %-15s %-25s\n",
            $dto->loadingAddress->zipCode,
            $dto->loadingAddress->city,
            $dto->deliveryAddress->zipCode,
            $dto->deliveryAddress->city
        );
        $label .= str_repeat('-', 81) . "\n";

        $count = 1;
        foreach ($dto->lines as $line) {
            $label .= sprintf("| %3d | %-25s | %2d\n", $count, $line->description, $line->quantity);
            $count += 1;
        }
        $label .= str_repeat('-', 81) . "\n";

        $column = 1;
        foreach ($dto->ssccs as $sscc) {
            if ($column > 1) {
                $label .= ' | ';
            }
            $label .= sprintf("%18s", $sscc->code);
            $column += 1;
            if ($column > 4) {
                $label .= "\n";
                $column = 1;
            }
        }

        return $label;
    }
}
