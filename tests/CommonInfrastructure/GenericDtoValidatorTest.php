<?php

declare(strict_types=1);

namespace App\Tests\CommonInfrastructure;

use App\CommonInfrastructure\GenericDtoValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GenericDtoValidatorTest extends TestCase
{
    private GenericDtoValidator $sut;

    private MockObject|ValidatorInterface $symfonyValidator;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->symfonyValidator = $this->createMock(ValidatorInterface::class);

        $this->sut = new GenericDtoValidator($this->symfonyValidator);
    }

    #[Test]
    public function doValidationIfDtoNotRegistered(): void
    {
        $this->symfonyValidator->expects(self::once())->method('validate');

        $dto = new TestDto(1, 'name');

        $this->sut->validate($dto, 'methodName');
    }

    #[Test]
    public function doNotRepeatValidation(): void
    {
        $this->symfonyValidator->expects(self::never())->method('validate');

        $dto = new TestDto(1, 'name');
        GenericDtoValidator::registerValidation($dto);

        $this->sut->validate($dto, 'methodName');
    }
}

// phpcs:disable
class TestDto
{
    public readonly int $id;
    public readonly string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}
// phpcs:enable
