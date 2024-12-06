<?php

declare(strict_types=1);

namespace App\Tests\Order\Application\Entity;

use App\Order\Domain\Exception\OrderDomainException;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Order\Domain\OrderStatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class OrderTest extends TestCase
{
    private Order $sut;

    private const NUMBER = '123/12345678/456';

    protected function setUp(): void
    {
        $this->sut = new Order(self::NUMBER);
    }

    #[Test]
    public function incrementVersion(): void
    {
        $this->sut->incrementVersion();

        self::assertEquals(1, $this->sut->getVersion());
    }

    #[Test]
    public function addLine(): void
    {
        $line1 = (new OrderLine())
            ->setQuantity(3);
        $line2 = (new OrderLine())
            ->setQuantity(5);

        $this->sut->addLine($line1);
        $this->sut->addLine($line2);

        self::assertEquals(8, $this->sut->getQuantityTotal());
        self::assertEquals(2, $this->sut->getLines()->count());
        self::assertSame($this->sut, $line1->getOrder());
        self::assertSame($this->sut, $line2->getOrder());
    }

    #[Test]
    public function removeLine(): void
    {
        $line1 = (new OrderLine())
            ->setQuantity(3)
            ->setGoodsDescription('computers');
        $line2 = (new OrderLine())
            ->setQuantity(5)
            ->setGoodsDescription('printers');

        $this->sut->addLine($line1);
        $this->sut->addLine($line2);

        $this->sut->removeLine($line1);

        self::assertEquals(5, $this->sut->getQuantityTotal());
        self::assertEquals(1, $this->sut->getLines()->count());
        $values = array_values($this->sut->getLines()->toArray());
        self::assertEquals('printers', $values[0]->getGoodsDescription());
    }

    #[Test]
    public function removeLineNoChange(): void
    {
        $line1 = (new OrderLine())
            ->setQuantity(3)
            ->setGoodsDescription('computers');
        $line2 = (new OrderLine())
            ->setQuantity(5)
            ->setGoodsDescription('printers');

        $this->sut->addLine($line1);

        $this->sut->removeLine($line2);

        self::assertEquals(3, $this->sut->getQuantityTotal());
        self::assertEquals(1, $this->sut->getLines()->count());
        $values = array_values($this->sut->getLines()->toArray());
        self::assertEquals('computers', $values[0]->getGoodsDescription());
    }

    #[Test]
    public function updateLine(): void
    {
        $line1 = (new OrderLine())
            ->setQuantity(3)
            ->setLength(101)
            ->setWidth(102)
            ->setHeight(103)
            ->setWeightOnePallet(20000)
            ->setWeightTotal(60000)
            ->setGoodsDescription('computers');
        $line2 = (new OrderLine())
            ->setQuantity(1)
            ->setLength(104)
            ->setWidth(105)
            ->setHeight(106)
            ->setWeightOnePallet(20100)
            ->setWeightTotal(20100)
            ->setGoodsDescription('printers');

        $this->sut->addLine($line1);
        $this->sut->addLine($line2);

        $lineUpd = (new OrderLine())
            ->setQuantity(3)
            ->setLength(204)
            ->setWidth(205)
            ->setHeight(206)
            ->setWeightOnePallet(22000)
            ->setWeightTotal(66000)
            ->setGoodsDescription('printers 2');

        $this->sut->updateLine(1, $lineUpd);

        self::assertEquals(6, $this->sut->getQuantityTotal());
        self::assertEquals(2, $this->sut->getLines()->count());
        $lineChk = $this->sut->getLines()->toArray()[1];
        self::assertEquals(3, $lineChk->getQuantity());
        self::assertEquals(204, $lineChk->getLength());
        self::assertEquals(205, $lineChk->getWidth());
        self::assertEquals(206, $lineChk->getHeight());
        self::assertEquals(22000, $lineChk->getWeightOnePallet());
        self::assertEquals(66000, $lineChk->getWeightTotal());
        self::assertEquals('printers 2', $lineChk->getGoodsDescription());
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    #[Test, DataProvider('dataProviderChangeStatus')]
    public function changeStatus(
        string $current,
        string $new,
        string $expectedException
    ): void {
        if ($expectedException) {
            self::expectException(OrderDomainException::class);
            self::expectExceptionMessageMatches('/^' . $expectedException . '$/');
        } else {
            self::expectNotToPerformAssertions();
        }

        $prop = new ReflectionProperty($this->sut, 'status');
        $prop->setValue($this->sut, OrderStatusEnum::from($current));

        $this->sut->changeStatus(OrderStatusEnum::from($new));
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public static function dataProviderChangeStatus(): array
    {
        return [
            'invalid-from' => [
                'current' => 'DELIVERED',
                'new' => 'DELIVERED',
                'expectedException' => 'ORDER_INVALID_STATUS_TRANSITION',
            ],
            'invalid-to' => [
                'current' => 'NEW',
                'new' => 'DELIVERED',
                'expectedException' => 'ORDER_INVALID_STATUS_TRANSITION',
            ],
            'new-sent' => [
                'current' => 'NEW',
                'new' => 'SENT',
                'expectedException' => '',
            ],
            'confirmed-printed' => [
                'current' => 'CONFIRMED',
                'new' => 'PRINTED',
                'expectedException' => '',
            ],
        ];
    }
}
