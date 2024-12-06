<?php

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace App\Tests\Order\Application\Validator;

use App\CommonInfrastructure\Api\ApiProblemException;
use App\Order\Domain\FixedAddress;
use App\Order\Application\Validator\FixedAddressValidator;
use App\Order\Infrastructure\Repository\FixedAddressRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RepositoryMock\RepositoryMockObject;
use RepositoryMock\RepositoryMockTrait;

class FixedAddressValidatorTest extends TestCase
{
    use RepositoryMockTrait;

    private FixedAddressValidator $sut;

    private FixedAddressRepository|RepositoryMockObject $addressRepository;

    protected function setUp(): void
    {
        $this->addressRepository = $this->createRepositoryMock(FixedAddressRepository::class);

        $this->sut = new FixedAddressValidator($this->addressRepository);
    }

    #[Test]
    #[DataProvider('dataProviderValidateExists')]
    public function validateExists(
        ?FixedAddress $address,
        string $expected
    ): void {
        if ($expected) {
            $this->expectException(ApiProblemException::class);
            $this->expectExceptionMessageMatches('/^' . $expected . '$/');
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->sut->validateExists($address);
    }

    /**
     * @return array<string, mixed>
     */
    public static function dataProviderValidateExists(): array
    {
        return [
            'null' => [
                'address' => null,
                'expected' => 'ORDER_FIXEDADDRESS_NOT_FOUND',
            ],
            'valid' => [
                'address' => new FixedAddress(),
                'expected' => '',
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderValidateExternalIdNotUsed')]
    public function validateExternalIdNotUsed(
        string $externalId,
        string $expected
    ): void {
        $this->addressRepository->loadStore([
            [
                'id' => 1,
                'externalId' => 'WH1',
            ]
        ]);

        if ($expected) {
            $this->expectException(ApiProblemException::class);
            $this->expectExceptionMessageMatches('/^' . $expected . '$/');
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->sut->validateExternalIdNotUsed($externalId);
    }

    /**
     * @return array<string, mixed>
     */
    public static function dataProviderValidateExternalIdNotUsed(): array
    {
        return [
            'id-not-used' => [
                'externalId' => 'AAA',
                'expected' => '',
            ],
            'conflict' => [
                'externalId' => 'WH1',
                'expected' => 'ORDER_FIXEDADDRESS_EXTERNAL_ID_ALREADY_EXIST',
            ],
        ];
    }
}
