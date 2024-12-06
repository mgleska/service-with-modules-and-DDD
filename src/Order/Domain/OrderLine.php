<?php

declare(strict_types=1);

namespace App\Order\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[ORM\Entity]
#[ORM\Table(name: "ord_order_line")]
#[Exclude]
class OrderLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'lines')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false)]
    private Order $order;

    #[ORM\Column]
    private int $quantity;

    // pallet length in [cm]
    #[ORM\Column]
    private int $length;

    // pallet width in [cm]
    #[ORM\Column]
    private int $width;

    // pallet height in [cm]
    #[ORM\Column]
    private int $height;

    // pallet weight in [kg] multiplied by 100
    #[ORM\Column]
    private int $weightOnePallet;

    // total weight of all pallets of order line, in [kg] multiplied by 100
    #[ORM\Column]
    private int $weightTotal;

    #[ORM\Column(length: 250)]
    private string $goodsDescription;

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $this->length = $length;
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function getWeightOnePallet(): int
    {
        return $this->weightOnePallet;
    }

    public function setWeightOnePallet(int $weightOnePallet): static
    {
        $this->weightOnePallet = $weightOnePallet;
        return $this;
    }

    public function getWeightTotal(): int
    {
        return $this->weightTotal;
    }

    public function setWeightTotal(int $weightTotal): static
    {
        $this->weightTotal = $weightTotal;
        return $this;
    }

    public function getGoodsDescription(): string
    {
        return $this->goodsDescription;
    }

    public function setGoodsDescription(string $goodsDescription): static
    {
        $this->goodsDescription = $goodsDescription;
        return $this;
    }
}
