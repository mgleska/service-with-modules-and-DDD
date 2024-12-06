<?php

declare(strict_types=1);

namespace App\Order\Domain;

use App\Order\Infrastructure\Repository\FixedAddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[ORM\Entity(repositoryClass: FixedAddressRepository::class)]
#[ORM\Table(name: "ord_fixed_address")]
#[Exclude]
class FixedAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $version;

    #[ORM\Column(length: 100)]
    private string $externalId;

    #[ORM\Column(length: 250)]
    private string $nameCompanyOrPerson;

    #[ORM\Column(length: 250)]
    private string $address;

    #[ORM\Column(length: 250)]
    private string $city;

    #[ORM\Column(length: 50)]
    private string $zipCode;

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

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): static
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getNameCompanyOrPerson(): string
    {
        return $this->nameCompanyOrPerson;
    }

    public function setNameCompanyOrPerson(string $nameCompanyOrPerson): static
    {
        $this->nameCompanyOrPerson = $nameCompanyOrPerson;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function incrementVersion(): void
    {
        $this->version += 1;
    }
}
