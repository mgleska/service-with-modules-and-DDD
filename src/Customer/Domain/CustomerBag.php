<?php

declare(strict_types=1);

namespace App\Customer\Domain;

class CustomerBag
{
    private int $customerId;
    private string $databaseSuffix;
    private string $name;

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): static
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getDatabaseSuffix(): string
    {
        return $this->databaseSuffix;
    }

    public function setDatabaseSuffix(string $databaseSuffix): static
    {
        $this->databaseSuffix = $databaseSuffix;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
