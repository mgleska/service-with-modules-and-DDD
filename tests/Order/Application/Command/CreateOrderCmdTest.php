<?php

declare(strict_types=1);

namespace App\Tests\Order\Application\Command;

use App\Auth\Domain\UserBag;
use App\CommonInfrastructure\GenericDtoValidator;
use App\Order\Application\Command\CreateOrderCmd;
use App\Order\Application\Validator\FixedAddressValidator;
use App\Order\Application\Validator\OrderValidator;
use App\Order\Application\Dto\Order\CreateOrderDto;
use App\Order\Application\Dto\Order\OrderAddressContactDto;
use App\Order\Application\Dto\Order\OrderAddressDto;
use App\Order\Application\Dto\Order\OrderLineDto;
use App\Order\Domain\FixedAddress;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Order\Domain\OrderStatusEnum;
use App\Order\Infrastructure\Repository\FixedAddressRepository;
use App\Order\Infrastructure\Repository\OrderRepository;
use App\Tests\AutoincrementIdTrait;
use DateMalformedStringException;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionException;
use RepositoryMock\RepositoryMockTrait;

class CreateOrderCmdTest extends TestCase
{
    use RepositoryMockTrait;
    use AutoincrementIdTrait;

    private CreateOrderCmd $sut;

    private MockObject|OrderRepository $orderRepository;
    private MockObject|LoggerInterface $logger;
    private MockObject|UserBag $userBag;
    private MockObject|FixedAddressRepository $addressRepository;

    private const CUSTOMER_ID = 11;

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userBag = $this->createMock(UserBag::class);
        $this->addressRepository = $this->createMock(FixedAddressRepository::class);

        $this->userBag->method('getCustomerId')->willReturn(self::CUSTOMER_ID);
        self::setAutoincrementIdForClass(Order::class, 5);

        $this->sut = new CreateOrderCmd(
            $this->orderRepository,
            $this->createMock(OrderValidator::class),
            $this->logger,
            $this->userBag,
            $this->createMock(GenericDtoValidator::class),
            $this->addressRepository,
            $this->createMock(FixedAddressValidator::class)
        );
    }

    /**
     * @param array<string, mixed> $dtoData
     * @param array<string, mixed> $expectedOrder
     * @param array<int, array<string, mixed>> $expectedLines
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @throws DateMalformedStringException
     */
    #[Test]
    #[DataProvider('dataProviderCreateOrder')]
    public function createOrder(
        array $dtoData,
        ?FixedAddress $fixedAddress,
        int $expectedId,
        array $expectedOrder,
        array $expectedLines
    ): void {
        $dtoData['loadingAddress'] = $dtoData['loadingAddress'] ? new OrderAddressDto(...$dtoData['loadingAddress']) : null;
        $dtoData['loadingContact'] = new OrderAddressContactDto(...$dtoData['loadingContact']);
        $dtoData['deliveryAddress'] = new OrderAddressDto(...$dtoData['deliveryAddress']);
        $dtoData['deliveryContact'] = new OrderAddressContactDto(...$dtoData['deliveryContact']);
        $lines = [];
        foreach ($dtoData['lines'] as $line) {
            $lines[] = new OrderLineDto(...$line);
        }
        $dtoData['lines'] = $lines;
        $dto = new CreateOrderDto(...$dtoData);

        $this->logger->expects(self::once())->method('info');

        $this->addressRepository->method('findOneBy')->willReturn($fixedAddress);

        $storedOrder = null;
        $this->orderRepository->method('save')->willReturnCallback(
            function (Order $order) use (&$storedOrder) {
                self::setAutoincrementId($order);
                foreach ($order->getLines() as $line) {
                    self::setAutoincrementId($line);
                }
                $order->incrementVersion();
                $storedOrder = unserialize(serialize($order));
            }
        );

        $result = $this->sut->createOrder($dto);

        $this->assertSame($expectedId, $result);

        $this->assertSame($expectedId, $storedOrder->getId());
        $this->assertSame(1, $storedOrder->getVersion());
        $this->assertMatchesRegularExpression($expectedOrder['number'], $storedOrder->getNumber());
        $this->assertSame($expectedOrder['status'], $storedOrder->getStatus());
        $this->assertSame($expectedOrder['quantityTotal'], $storedOrder->getQuantityTotal());
        $this->assertEquals(new DateTime($expectedOrder['loadingDate']), $storedOrder->getLoadingDate());
        $this->assertSame($expectedOrder['loadingNameCompanyOrPerson'], $storedOrder->getLoadingNameCompanyOrPerson());
        $this->assertSame($expectedOrder['loadingAddress'], $storedOrder->getLoadingAddress());
        $this->assertSame($expectedOrder['loadingCity'], $storedOrder->getLoadingCity());
        $this->assertSame($expectedOrder['loadingZipCode'], $storedOrder->getLoadingZipCode());
        $this->assertSame($expectedOrder['loadingContactPerson'], $storedOrder->getLoadingContactPerson());
        $this->assertSame($expectedOrder['loadingContactPhone'], $storedOrder->getLoadingContactPhone());
        $this->assertSame($expectedOrder['loadingContactEmail'], $storedOrder->getLoadingContactEmail());
        $this->assertSame($expectedOrder['loadingFixedAddressExternalId'], $storedOrder->getLoadingFixedAddressExternalId());
        $this->assertSame($expectedOrder['deliveryNameCompanyOrPerson'], $storedOrder->getDeliveryNameCompanyOrPerson());
        $this->assertSame($expectedOrder['deliveryAddress'], $storedOrder->getDeliveryAddress());
        $this->assertSame($expectedOrder['deliveryCity'], $storedOrder->getDeliveryCity());
        $this->assertSame($expectedOrder['deliveryZipCode'], $storedOrder->getDeliveryZipCode());
        $this->assertSame($expectedOrder['deliveryContactPerson'], $storedOrder->getDeliveryContactPerson());
        $this->assertSame($expectedOrder['deliveryContactPhone'], $storedOrder->getDeliveryContactPhone());
        $this->assertSame($expectedOrder['deliveryContactEmail'], $storedOrder->getDeliveryContactEmail());

        $this->assertSame(count($expectedLines), count($storedOrder->getLines()));
        foreach ($storedOrder->getLines() as $orderLine) {
            $this->assertSame($expectedId, $orderLine->getOrder()->getId());
            $this->assertTrue(
                $this->isMatchingOrderLine($orderLine, $expectedLines),
                sprintf(
                    'OrderLine with id="%d" description="%s" not match expected.',
                    $orderLine->getId(),
                    $orderLine->getGoodsDescription()
                )
            );
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @throws ReflectionException
     */
    public static function dataProviderCreateOrder(): array
    {
        return [
            'explicit-loading-address-no-lines' => [
                'dtoData' => [
                    'loadingDate' => '2024-06-03',
                    'loadingFixedAddressExternalId' => null,
                    'loadingAddress' => [
                        'nameCompanyOrPerson' => 'Load Place',
                        'address' => 'ul. Garbary 125',
                        'city' => 'Poznań',
                        'zipCode' => '61-719'
                    ],
                    'loadingContact' => [
                        'contactPerson' => 'Contact Person',
                        'contactPhone' => '+48-222-222-222',
                        'contactEmail' => 'person@email.com',
                    ],
                    'deliveryAddress' => [
                        'nameCompanyOrPerson' => 'Delivery Place',
                        'address' => 'ul. Wschodnia',
                        'city' => 'Poznań',
                        'zipCode' => '61-001'
                    ],
                    'deliveryContact' => [
                        'contactPerson' => 'Person2',
                        'contactPhone' => '+48-111-111-111',
                        'contactEmail' => null,
                    ],
                    'lines' => [],
                ],
                'fixedAddress' => null,
                'expectedId' => 5,
                'expectedOrder' => [
                    'number' => '#^[0-9]+/[0-9]{8}/[0-9]+$#',
                    'status' => OrderStatusEnum::NEW,
                    'quantityTotal' => 0,
                    'loadingDate' => '2024-06-03',
                    'loadingNameCompanyOrPerson' => 'Load Place',
                    'loadingAddress' => 'ul. Garbary 125',
                    'loadingCity' => 'Poznań',
                    'loadingZipCode' => '61-719',
                    'loadingContactPerson' => 'Contact Person',
                    'loadingContactPhone' => '+48-222-222-222',
                    'loadingContactEmail' => 'person@email.com',
                    'loadingFixedAddressExternalId' => null,
                    'deliveryNameCompanyOrPerson' => 'Delivery Place',
                    'deliveryAddress' => 'ul. Wschodnia',
                    'deliveryCity' => 'Poznań',
                    'deliveryZipCode' => '61-001',
                    'deliveryContactPerson' => 'Person2',
                    'deliveryContactPhone' => '+48-111-111-111',
                    'deliveryContactEmail' => null,
                ],
                'expectedLines' => [],
            ],
            'fixed-loading-address-and-two-lines' => [
                'dtoData' => [
                    'loadingDate' => '2024-06-03',
                    'loadingFixedAddressExternalId' => 'WH1',
                    'loadingAddress' => null,
                    'loadingContact' => [
                        'contactPerson' => 'Contact Person',
                        'contactPhone' => '+48-222-222-222',
                        'contactEmail' => 'person@email.com',
                    ],
                    'deliveryAddress' => [
                        'nameCompanyOrPerson' => 'Delivery Place',
                        'address' => 'ul. Wschodnia',
                        'city' => 'Poznań',
                        'zipCode' => '61-001'
                    ],
                    'deliveryContact' => [
                        'contactPerson' => 'Person2',
                        'contactPhone' => '+48-111-111-111',
                        'contactEmail' => null,
                    ],
                    'lines' => [
                        [
                            'id' => null,
                            'quantity' => 3,
                            'length' => 120,
                            'width' => 80,
                            'height' => 100,
                            'weightOnePallet' => 200,
                            'weightTotal' => null,
                            'goodsDescription' => 'computers',
                        ],
                        [
                            'id' => null,
                            'quantity' => 5,
                            'length' => 120,
                            'width' => 80,
                            'height' => 100,
                            'weightOnePallet' => 200,
                            'weightTotal' => null,
                            'goodsDescription' => 'printers',
                        ],
                    ],
                ],
                'fixedAddress' => self::createFakeObject(FixedAddress::class, [
                    'id' => 20,
                    'externalId' => 'WH1',
                    'nameCompanyOrPerson' => 'Warehouse 1',
                    'address' => 'Dworcowa 2',
                    'city' => 'Poznań',
                    'zipCode' => '61-801'
                ]),
                'expectedId' => 5,
                'expectedOrder' => [
                    'number' => '#^[0-9]+/[0-9]{8}/[0-9]+$#',
                    'status' => OrderStatusEnum::NEW,
                    'quantityTotal' => 8,
                    'loadingDate' => '2024-06-03',
                    'loadingNameCompanyOrPerson' => 'Warehouse 1',
                    'loadingAddress' => 'Dworcowa 2',
                    'loadingCity' => 'Poznań',
                    'loadingZipCode' => '61-801',
                    'loadingContactPerson' => 'Contact Person',
                    'loadingContactPhone' => '+48-222-222-222',
                    'loadingContactEmail' => 'person@email.com',
                    'loadingFixedAddressExternalId' => 'WH1',
                    'deliveryNameCompanyOrPerson' => 'Delivery Place',
                    'deliveryAddress' => 'ul. Wschodnia',
                    'deliveryCity' => 'Poznań',
                    'deliveryZipCode' => '61-001',
                    'deliveryContactPerson' => 'Person2',
                    'deliveryContactPhone' => '+48-111-111-111',
                    'deliveryContactEmail' => null,
                ],
                'expectedLines' => [
                    [
                        'quantity' => 3,
                        'length' => 120,
                        'width' => 80,
                        'height' => 100,
                        'weightOnePallet' => 20000,
                        'weightTotal' => 60000,
                        'goodsDescription' => 'computers',
                    ],
                    [
                        'quantity' => 5,
                        'length' => 120,
                        'width' => 80,
                        'height' => 100,
                        'weightOnePallet' => 20000,
                        'weightTotal' => 100000,
                        'goodsDescription' => 'printers',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $expectedLines
     */
    private function isMatchingOrderLine(OrderLine $entity, array $expectedLines): bool
    {
        $matchFound = false;
        foreach ($expectedLines as $line) {
            if (
                $line['quantity'] !== $entity->getQuantity()
                || $line['length'] !== $entity->getLength()
                || $line['width'] !== $entity->getWidth()
                || $line['height'] !== $entity->getHeight()
                || $line['weightOnePallet'] !== $entity->getWeightOnePallet()
                || $line['weightTotal'] !== $entity->getWeightTotal()
                || $line['goodsDescription'] !== $entity->getGoodsDescription()
            ) {
                continue;
            }
            $matchFound = true;
            break;
        }

        return $matchFound;
    }
}
