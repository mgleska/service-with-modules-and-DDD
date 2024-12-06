<?php

declare(strict_types=1);

namespace App\Order\Domain;

use App\Order\Domain\Exception\OrderDomainException;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

use function array_key_exists;
use function in_array;

// root entity of aggregate

#[ORM\Entity]
#[ORM\Table(name: "ord_order")]
#[Exclude]
class Order
{
    private const STATUS_TRANSITIONS = [
        OrderStatusEnum::NEW->value => [OrderStatusEnum::SENT],
        OrderStatusEnum::CONFIRMED->value => [OrderStatusEnum::PRINTED],
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $version;

    #[ORM\Column(length: 50)]
    private string $number;

    #[ORM\Column(length: 20, enumType: OrderStatusEnum::class)]
    private OrderStatusEnum $status;

    #[ORM\Column]
    private int $quantityTotal;

    #[ORM\Column(type: 'date')]
    private DateTime $loadingDate;

    #[ORM\Column(length: 250)]
    private string $loadingNameCompanyOrPerson;

    #[ORM\Column(length: 250)]
    private string $loadingAddress;

    #[ORM\Column(length: 250)]
    private string $loadingCity;

    #[ORM\Column(length: 50)]
    private string $loadingZipCode;

    #[ORM\Column(length: 250)]
    private string $loadingContactPerson;

    #[ORM\Column(length: 250)]
    private string $loadingContactPhone;

    #[ORM\Column(length: 250, nullable: true)]
    private ?string $loadingContactEmail = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $loadingFixedAddressExternalId = null;

    #[ORM\Column(length: 250)]
    private string $deliveryNameCompanyOrPerson;

    #[ORM\Column(length: 250)]
    private string $deliveryAddress;

    #[ORM\Column(length: 250)]
    private string $deliveryCity;

    #[ORM\Column(length: 50)]
    private string $deliveryZipCode;

    #[ORM\Column(length: 250)]
    private string $deliveryContactPerson;

    #[ORM\Column(length: 250)]
    private string $deliveryContactPhone;

    #[ORM\Column(length: 250, nullable: true)]
    private ?string $deliveryContactEmail = null;

    /** @var Collection<int, OrderLine> */
    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'order', cascade: ['persist', 'remove'])]
    private Collection $lines;

    /** @var Collection<int, OrderSscc> */
    #[ORM\OneToMany(targetEntity: OrderSscc::class, mappedBy: 'order', cascade: ['persist'])]
    private Collection $ssccs;

    public function __construct(string $number)
    {
        $this->number = $number;
        $this->status = OrderStatusEnum::NEW;
        $this->quantityTotal = 0;
        $this->version = 0;
        $this->lines = new ArrayCollection();
        $this->ssccs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getStatus(): OrderStatusEnum
    {
        return $this->status;
    }

    public function getQuantityTotal(): int
    {
        return $this->quantityTotal;
    }

    public function getLoadingNameCompanyOrPerson(): string
    {
        return $this->loadingNameCompanyOrPerson;
    }

    public function setLoadingNameCompanyOrPerson(string $loadingNameCompanyOrPerson): static
    {
        $this->loadingNameCompanyOrPerson = $loadingNameCompanyOrPerson;
        return $this;
    }

    public function getLoadingAddress(): string
    {
        return $this->loadingAddress;
    }

    public function setLoadingAddress(string $loadingAddress): static
    {
        $this->loadingAddress = $loadingAddress;
        return $this;
    }

    public function getLoadingCity(): string
    {
        return $this->loadingCity;
    }

    public function setLoadingCity(string $loadingCity): static
    {
        $this->loadingCity = $loadingCity;
        return $this;
    }

    public function getLoadingZipCode(): string
    {
        return $this->loadingZipCode;
    }

    public function setLoadingZipCode(string $loadingZipCode): static
    {
        $this->loadingZipCode = $loadingZipCode;
        return $this;
    }

    public function getLoadingContactPerson(): string
    {
        return $this->loadingContactPerson;
    }

    public function setLoadingContactPerson(string $loadingContactPerson): static
    {
        $this->loadingContactPerson = $loadingContactPerson;
        return $this;
    }

    public function getLoadingContactPhone(): string
    {
        return $this->loadingContactPhone;
    }

    public function setLoadingContactPhone(string $loadingContactPhone): static
    {
        $this->loadingContactPhone = $loadingContactPhone;
        return $this;
    }

    public function getLoadingContactEmail(): ?string
    {
        return $this->loadingContactEmail;
    }

    public function setLoadingContactEmail(?string $loadingContactEmail): static
    {
        $this->loadingContactEmail = $loadingContactEmail;
        return $this;
    }

    public function getDeliveryNameCompanyOrPerson(): string
    {
        return $this->deliveryNameCompanyOrPerson;
    }

    public function setDeliveryNameCompanyOrPerson(string $deliveryNameCompanyOrPerson): static
    {
        $this->deliveryNameCompanyOrPerson = $deliveryNameCompanyOrPerson;
        return $this;
    }

    public function getDeliveryAddress(): string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(string $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    public function getDeliveryCity(): string
    {
        return $this->deliveryCity;
    }

    public function setDeliveryCity(string $deliveryCity): static
    {
        $this->deliveryCity = $deliveryCity;
        return $this;
    }

    public function getDeliveryZipCode(): string
    {
        return $this->deliveryZipCode;
    }

    public function setDeliveryZipCode(string $deliveryZipCode): static
    {
        $this->deliveryZipCode = $deliveryZipCode;
        return $this;
    }

    public function getDeliveryContactPerson(): string
    {
        return $this->deliveryContactPerson;
    }

    public function setDeliveryContactPerson(string $deliveryContactPerson): static
    {
        $this->deliveryContactPerson = $deliveryContactPerson;
        return $this;
    }

    public function getDeliveryContactPhone(): string
    {
        return $this->deliveryContactPhone;
    }

    public function setDeliveryContactPhone(string $deliveryContactPhone): static
    {
        $this->deliveryContactPhone = $deliveryContactPhone;
        return $this;
    }

    public function getDeliveryContactEmail(): ?string
    {
        return $this->deliveryContactEmail;
    }

    public function setDeliveryContactEmail(?string $deliveryContactEmail): static
    {
        $this->deliveryContactEmail = $deliveryContactEmail;
        return $this;
    }

    public function getLoadingFixedAddressExternalId(): ?string
    {
        return $this->loadingFixedAddressExternalId;
    }

    public function setLoadingFixedAddressExternalId(?string $loadingFixedAddressExternalId): static
    {
        $this->loadingFixedAddressExternalId = $loadingFixedAddressExternalId;
        return $this;
    }

    public function getLoadingDate(): DateTime
    {
        return $this->loadingDate;
    }

    public function setLoadingDate(DateTime $loadingDate): static
    {
        $this->loadingDate = $loadingDate;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return Collection<int, OrderLine>
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    /**
     * @return Collection<int, OrderSscc>
     */
    public function getSsccs(): Collection
    {
        return $this->ssccs;
    }

    public function incrementVersion(): void
    {
        $this->version += 1;
    }

    public function addLine(OrderLine $line): static
    {
        $this->lines->add($line);
        $this->quantityTotal += $line->getQuantity();
        $line->setOrder($this);

        return $this;
    }

    public function removeLine(OrderLine $line): static
    {
        if ($this->lines->removeElement($line)) {
            $this->quantityTotal -= $line->getQuantity();
        }

        return $this;
    }

    public function updateLine(int $index, OrderLine $newValues): static
    {
        $line = $this->lines->get($index);
        $this->quantityTotal -= $line->getQuantity() - $newValues->getQuantity();

        $line->setQuantity($newValues->getQuantity());
        $line->setLength($newValues->getLength());
        $line->setWidth($newValues->getWidth());
        $line->setHeight($newValues->getHeight());
        $line->setWeightOnePallet($newValues->getWeightOnePallet());
        $line->setWeightTotal($newValues->getWeightTotal());
        $line->setGoodsDescription($newValues->getGoodsDescription());

        return $this;
    }

    public function changeStatus(OrderStatusEnum $newStatus): void
    {
        if (
            array_key_exists($this->status->value, self::STATUS_TRANSITIONS)
            && in_array($newStatus, self::STATUS_TRANSITIONS[$this->status->value], true)
        ) {
            $this->status = $newStatus;
            return;
        }

        throw new OrderDomainException('ORDER_INVALID_STATUS_TRANSITION');
    }
}
