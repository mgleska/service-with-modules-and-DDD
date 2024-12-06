<?php

declare(strict_types=1);

namespace App\Customer\Application\Dto;

use App\Customer\Domain\Customer;

class CustomerDto
{
    public readonly int $id;
    public readonly int $version;
    public readonly string $name;
    public readonly string $dbNameSuffix;

    public function __construct(int $id, int $version, string $name, string $dbNameSuffix)
    {
        $this->id = $id;
        $this->version = $version;
        $this->name = $name;
        $this->dbNameSuffix = $dbNameSuffix;
    }

    public static function fromEntity(Customer $entity): self
    {
        return new self(
            $entity->getId(),
            $entity->getVersion(),
            $entity->getName(),
            $entity->getDbNameSuffix()
        );
    }
}
