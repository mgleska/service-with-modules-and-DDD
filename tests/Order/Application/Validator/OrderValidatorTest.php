<?php

declare(strict_types=1);

namespace App\Tests\Order\Application\Validator;

use App\Auth\Domain\UserBag;
use App\CommonInfrastructure\Api\ApiProblemException;
use App\Order\Application\Validator\OrderValidator;
use App\Order\Application\Dto\Order\OrderAddressDto;
use App\Order\Domain\FixedAddress;
use App\Order\Domain\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderValidatorTest extends TestCase
{
    private OrderValidator $sut;

    private MockObject|UserBag $userBag;

    private const CUSTOMER_ID = 1;

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function setUp(): void
    {
        $this->userBag = $this->createMock(UserBag::class);

        $this->userBag->method('getCustomerId')->willReturn(self::CUSTOMER_ID);

        $this->sut = new OrderValidator();
    }

    #[Test]
    #[DataProvider('dataProviderValidateExists')]
    public function validateExists(
        ?Order $order,
        string $expected
    ): void {
        if ($expected) {
            $this->expectException(ApiProblemException::class);
            $this->expectExceptionMessageMatches('/^' . $expected . '$/');
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->sut->validateExists($order);
    }

    /**
     * @return array<string, mixed>
     */
    public static function dataProviderValidateExists(): array
    {
        return [
            'null' => [
                'order' => null,
                'expected' => 'ORDER_ORDER_NOT_FOUND',
            ],
            'valid' => [
                'order' => new Order('number'),
                'expected' => '',
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderValidateLoadingAddressForCreate')]
    public function validateLoadingAddressForCreate(
        ?FixedAddress $fixedAddress,
        ?OrderAddressDto $addressDto,
        string $expectedExceptionMsg
    ): void {
        if ($expectedExceptionMsg) {
            $this->expectException(ApiProblemException::class);
            $this->expectExceptionMessageMatches('/^' . $expectedExceptionMsg . '$/');
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->sut->validateLoadingAddressForCreate($fixedAddress, $addressDto);
    }

    /**
     * @return array<string, array<string, mixed>>
     * @noinspection PhpUnhandledExceptionInspection
     */
    public static function dataProviderValidateLoadingAddressForCreate(): array
    {
        return [
            'null-null' => [
                'fixedAddress' => null,
                'addressDto' => null,
                'expectedExceptionMsg' => 'ORDER_CREATE_LOADING_ADDRESS_NOT_SPECIFIED',
            ],
            'set-both' => [
                'fixedAddress' => new FixedAddress(),
                'addressDto' => new OrderAddressDto('', '', '', ''),
                'expectedExceptionMsg' => 'ORDER_CREATE_LOADING_ADDRESS_SPECIFIED_BY_EXTERNAL_ID_AND_BY_VALUE',
            ],
            'fixed-address-not-requested' => [
                'fixedAddress' => null,
                'addressDto' => new OrderAddressDto('', '', '', ''),
                'expectedExceptionMsg' => '',
            ],
            'valid-external-id' => [
                'fixedAddress' => new FixedAddress(),
                'addressDto' => null,
                'expectedExceptionMsg' => '',
            ],
        ];
    }
}
