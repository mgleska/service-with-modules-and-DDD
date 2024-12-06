<?php

declare(strict_types=1);

namespace App\CommonInfrastructure;

use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_key_exists;
use function get_class;
use function spl_object_id;

class GenericDtoValidator
{
    /** @var bool[] */
    private static array $alreadyValidated = [];

    private readonly ValidatorInterface $symfonyValidator;

    public function __construct(
        ValidatorInterface $symfonyValidator,
    ) {
        $this->symfonyValidator = $symfonyValidator;
    }

    /**
     * @throws ValidationFailedException
     */
    public function validate(object $dto, string $methodName): void
    {
        $key = spl_object_id($dto) . '#' . get_class($dto);
        if (array_key_exists($key, self::$alreadyValidated)) {
            return;
        }

        $errors = $this->symfonyValidator->validate($dto);
        if ($errors->count() > 0) {
            throw new ValidationFailedException($methodName, $errors);
        }

        self::$alreadyValidated[$key] = true;
    }

    /**
     * This function should be used in attribute of DTO class. Like this:
     *
     *    #[Assert\Callback([GenericDtoValidator::class, 'registerValidation'])]
     *    class SomethingDto
     *    {
     *      ...
     *    }
     */
    public static function registerValidation(mixed $dto): void
    {
        $key = spl_object_id($dto) . '#' . get_class($dto);
        self::$alreadyValidated[$key] = true;
    }
}
