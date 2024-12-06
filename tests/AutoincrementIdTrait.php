<?php

declare(strict_types=1);

namespace App\Tests;

use ReflectionException;
use ReflectionProperty;

trait AutoincrementIdTrait
{
    /**
     * @var int[]
     */
    private static array $autoincrementIdForClass = [];

    private function setAutoincrementIdForClass(string $className, int $id): void
    {
        self::$autoincrementIdForClass[$className] = $id;
    }

    private static function getNextAutoincrementId(string $className): int
    {
        if (! array_key_exists($className, self::$autoincrementIdForClass)) {
            self::$autoincrementIdForClass[$className] = 1;
        }

        return self::$autoincrementIdForClass[$className]++;
    }

    /**
     * @throws ReflectionException
     */
    private static function setAutoincrementId(object $obj): void
    {
        $className = get_class($obj);
        $idReflection = new ReflectionProperty($obj, 'id');
        if ($idReflection->isInitialized($obj) === false || is_null($idReflection->getValue($obj))) {
            $idReflection->setValue($obj, self::getNextAutoincrementId($className));
        }
    }
}
