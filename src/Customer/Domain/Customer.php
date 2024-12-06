<?php

declare(strict_types=1);

namespace App\Customer\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[ORM\Entity]
#[ORM\Table(name: "cst_customer")]
#[Exclude]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $version;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 50)]
    private string $dbNameSuffix;

    public function __construct()
    {
        $this->version = 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVersion(): int
    {
        return $this->version;
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

    public function getDbNameSuffix(): string
    {
        return $this->dbNameSuffix;
    }

    public function setDbNameSuffix(string $dbNameSuffix): void
    {
        $this->dbNameSuffix = $dbNameSuffix;
    }

    public function incrementVersion(): void
    {
        $this->version += 1;
    }
}
