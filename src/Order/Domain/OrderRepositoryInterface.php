<?php

declare(strict_types=1);

namespace App\Order\Domain;

use Exception;

interface OrderRepositoryInterface
{
    /**
     * @throws Exception
     */
    public function save(Order $entity, bool $flush = false): void;

    public function removeLine(OrderLine $line): void;

    public function getWithLock(int $id): Order|null;

    /**
     * @param array<string, mixed> $criteria
     */
    public function count(array $criteria = []): int;
}
