<?php

declare(strict_types=1);

namespace App\Order\Domain;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

use function sprintf;

#[ORM\Entity]
#[ORM\Table(name: "ord_order_sscc")]
#[Exclude]
class OrderSscc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'ssccs')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false)]
    private Order $order;

    #[ORM\Column(type: Types::BIGINT)]
    private int $code;

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

    public function getCode(): string
    {
        return sprintf('%018d', $this->code);
    }

    public function setCode(string $code): static
    {
        $this->code = (int)$code;
        return $this;
    }
}
