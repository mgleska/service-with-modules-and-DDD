<?php

declare(strict_types=1);

namespace App\Tests\Order\Application\Command;

use App\CommonInfrastructure\Api\ApiProblemException;
use App\CommonInfrastructure\GenericDtoValidator;
use App\Order\Application\Command\UpdateOrderLinesCmd;
use App\Order\Application\Validator\OrderValidator;
use App\Order\Application\Dto\Order\OrderDto;
use App\Order\Application\Dto\Order\OrderLineDto;
use App\Order\Application\Dto\Order\UpdateOrderLinesDto;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Order\Domain\OrderStatusEnum;
use App\Order\Infrastructure\Repository\OrderRepository;
use App\Tests\AutoincrementIdTrait;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RepositoryMock\RepositoryMockTrait;

class UpdateOrderLinesCmdTest extends TestCase
{
    use RepositoryMockTrait;
    use AutoincrementIdTrait;

    private UpdateOrderLinesCmd $sut;

    private MockObject|OrderRepository $orderRepository;
    private MockObject|LoggerInterface $logger;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sut = new UpdateOrderLinesCmd(
            $this->orderRepository,
            $this->createMock(OrderValidator::class),
            $this->logger,
            $this->createMock(GenericDtoValidator::class)
        );
    }

    /**
     * @param array<string, mixed> $dtoData
     * @param array<string, mixed> $orderData
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    #[Test, DataProvider('dataProviderUpdateOrderLines')]
    public function updateOrderLines(
        array $dtoData,
        array $orderData,
        bool $expectedStatus,
        string $expectedMessage,
        string $expectedException,
        bool $isSaveExpected
    ): void {
        $this->orderRepository->method('getWithLock')->willReturn($this->createFakeObject(Order::class, $orderData));
        $dto = $this->createUpdateDto($dtoData);

        if ($expectedException) {
            $this->expectException(ApiProblemException::class);
            $this->expectExceptionMessageMatches('/^' . $expectedException . '$/');
        }

        $storedOrder = null;
        if ($isSaveExpected) {
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
        }

        if ($expectedStatus) {
            $this->logger->expects(self::once())->method('info');
        }

        $result = $this->sut->updateOrderLines($dto);

        self::assertIsArray($result);
        self::assertEquals($expectedStatus, $result[0]);
        if (is_string($result[1])) {
            self::assertEquals($expectedMessage, $result[1]);
        } else {
            self::assertInstanceOf(OrderDto::class, $result[1]);
            $resultDto = $result[1];
            $expectedVersion = $isSaveExpected ? $dtoData['version'] + 1 : $dtoData['version'];
            self::assertEquals($expectedVersion, $resultDto->version);
            self::assertEquals(count($dtoData['lines']), count($resultDto->lines));
            if ($isSaveExpected) {
                self::assertEquals($expectedVersion, $storedOrder->getVersion());
                self::assertEquals(count($dtoData['lines']), count($storedOrder->getLines()));
            }
            foreach ($dtoData['lines'] as $data) {
                $lineDto = $this->findInLinesDto($data['id'], $data['goodsDescription'], $resultDto->lines);
                self::assertNotNull($lineDto);
                self::assertEquals($data['quantity'], $lineDto->quantity);
                if ($isSaveExpected) {
                    $lineEntity = $this->findInLines($data['id'], $data['goodsDescription'], $storedOrder->getLines()->toArray());
                    self::assertNotNull($lineEntity);
                    self::assertEquals($data['quantity'], $lineEntity->getQuantity());
                }
            }
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     */
    public static function dataProviderUpdateOrderLines(): array
    {
        return [
            'version-diff' => [
                'dtoData' => ['version' => 1],
                'orderData' => ['version' => 2],
                'expectedStatus' => false,
                'expectedMessage' => 'ORDER_VERSION_IN_DATABASE_IS_DIFFERENT',
                'expectedException' => '',
                'isSaveExpected' => false,
            ],
            'wrong-status' => [
                'dtoData' => ['version' => 1],
                'orderData' => ['version' => 1, 'status' => OrderStatusEnum::CANCELLED],
                'expectedStatus' => false,
                'expectedMessage' => 'ORDER_STATUS_NOT_VALID_FOR_UPDATE_LINES',
                'expectedException' => '',
                'isSaveExpected' => false,
            ],
            'exception' => [
                'dtoData' => ['version' => 1, 'lines' => [['id' => 5]]],
                'orderData' => ['version' => 1, 'status' => OrderStatusEnum::NEW, 'lines' => []],
                'expectedStatus' => false,
                'expectedMessage' => '',
                'expectedException' => 'ORDER_UPDATE_LINES_LINE_WITH_INVALID_ID',
                'isSaveExpected' => false,
            ],
            'add-line' => [
                'dtoData' => [
                    'version' => 1,
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 5, 'goodsDescription' => 'computers'],
                        ['id' => null, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 2, 'goodsDescription' => 'printers'],
                    ],
                ],
                'orderData' => [
                    'id' => 7,
                    'version' => 1,
                    'number' => '123/12345678/456',
                    'status' => OrderStatusEnum::NEW,
                    'quantityTotal' => 5,
                    'loadingDate' => new DateTime('2024-06-03'),
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
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 5, 'goodsDescription' => 'computers'],
                    ],
                ],
                'expectedStatus' => true,
                'expectedMessage' => '',
                'expectedException' => '',
                'isSaveExpected' => true,
            ],
            'remove-line' => [
                'dtoData' => [
                    'version' => 1,
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 5, 'goodsDescription' => 'computers'],
                    ],
                ],
                'orderData' => [
                    'id' => 7,
                    'version' => 1,
                    'number' => '123/12345678/456',
                    'status' => OrderStatusEnum::NEW,
                    'quantityTotal' => 7,
                    'loadingDate' => new DateTime('2024-06-03'),
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
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 5, 'goodsDescription' => 'computers'],
                        ['id' => 11, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 2, 'goodsDescription' => 'printers'],
                    ],
                ],
                'expectedStatus' => true,
                'expectedMessage' => '',
                'expectedException' => '',
                'isSaveExpected' => true,
            ],
            'update-line' => [
                'dtoData' => [
                    'version' => 1,
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 5, 'goodsDescription' => 'computers'],
                        ['id' => 11, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 2, 'goodsDescription' => 'printers'],
                    ],
                ],
                'orderData' => [
                    'id' => 7,
                    'version' => 1,
                    'number' => '123/12345678/456',
                    'status' => OrderStatusEnum::NEW,
                    'quantityTotal' => 27,
                    'loadingDate' => new DateTime('2024-06-03'),
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
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 15, 'goodsDescription' => 'computers'],
                        ['id' => 11, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 12, 'goodsDescription' => 'printers'],
                    ],
                ],
                'expectedStatus' => true,
                'expectedMessage' => '',
                'expectedException' => '',
                'isSaveExpected' => true,
            ],
            'add-remove-update' => [
                'dtoData' => [
                    'version' => 1,
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 5, 'goodsDescription' => 'computers'],
                        ['id' => 11, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 2, 'goodsDescription' => 'printers'],
                        ['id' => null, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 3, 'goodsDescription' => 'keyboards'],                    ],
                ],
                'orderData' => [
                    'id' => 7,
                    'version' => 1,
                    'number' => '123/12345678/456',
                    'status' => OrderStatusEnum::NEW,
                    'quantityTotal' => 18,
                    'loadingDate' => new DateTime('2024-06-03'),
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
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 20000, 'weightTotal' => 100000, 'quantity' => 5, 'goodsDescription' => 'computers'],
                        ['id' => 11, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 20000, 'weightTotal' => 240000, 'quantity' => 12, 'goodsDescription' => 'printers'],
                        ['id' => 12, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 20000, 'weightTotal' => 20000, 'quantity' => 1, 'goodsDescription' => 'monitors'],
                    ],
                ],
                'expectedStatus' => true,
                'expectedMessage' => '',
                'expectedException' => '',
                'isSaveExpected' => true,
            ],
            'no-change' => [
                'dtoData' => [
                    'version' => 1,
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 5, 'goodsDescription' => 'computers'],
                        ['id' => 11, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 200, 'quantity' => 2, 'goodsDescription' => 'printers'],
                    ],
                ],
                'orderData' => [
                    'id' => 7,
                    'version' => 1,
                    'number' => '123/12345678/456',
                    'status' => OrderStatusEnum::NEW,
                    'quantityTotal' => 7,
                    'loadingDate' => new DateTime('2024-06-03'),
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
                    'lines' => [
                        ['id' => 10, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 20000, 'weightTotal' => 100000, 'quantity' => 5, 'goodsDescription' => 'computers'],
                        ['id' => 11, 'height' => 1, 'width' => 1, 'length' => 1, 'weightOnePallet' => 20000, 'weightTotal' => 100000, 'quantity' => 2, 'goodsDescription' => 'printers'],
                    ],
                ],
                'expectedStatus' => true,
                'expectedMessage' => '',
                'expectedException' => '',
                'isSaveExpected' => false,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createUpdateDto(array $data): UpdateOrderLinesDto
    {
        $lines = [];
        foreach ($data['lines'] ?? [] as $item) {
            $lines[] = $this->createOrderLineDto($item);
        }

        return new UpdateOrderLinesDto(
            $data['id'] ?? 1,
            $data['version'] ?? 1,
            $lines,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createOrderLineDto(array $data): OrderLineDto
    {
        return new OrderLineDto(
            $data['id'] ?? null,
            $data['quantity'] ?? 0,
            $data['length'] ?? 0,
            $data['width'] ?? 0,
            $data['height'] ?? 0,
            $data['weightOnePallet'] ?? 0,
            null,
            $data['goodsDescription'] ?? 'description',
        );
    }

    /**
     * @param OrderLine[] $lines
     */
    private function findInLines(?int $id, string $description, array $lines): ?OrderLine
    {
        foreach ($lines as $line) {
            if ($line->getId() === $id) {
                return $line;
            }
            if ($line->getGoodsDescription() === $description) {
                return $line;
            }
        }

        return null;
    }

    /**
     * @param OrderLineDto[] $lines
     */
    private function findInLinesDto(?int $id, string $description, array $lines): ?OrderLineDto
    {
        foreach ($lines as $line) {
            if ($line->id === $id) {
                return $line;
            }
            if ($line->goodsDescription === $description) {
                return $line;
            }
        }

        return null;
    }
}
