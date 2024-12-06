<?php

declare(strict_types=1);

namespace App\Order\Domain;

interface FixedAddressRepositoryInterface
{
    public function save(FixedAddress $entity, bool $flush = false): void;

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @return FixedAddress|null
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): object|null;
}
