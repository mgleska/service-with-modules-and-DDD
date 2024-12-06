<?php

declare(strict_types=1);

namespace App\Customer\Domain;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\LockMode;

interface CustomerRepositoryInterface
{
    public function save(Customer $entity, bool $flush = false): void;

    public function findById(int $id): Customer|null;

    /**
     * @throws DBALException
     */
    public function checkId(int $id): bool;

    /**
     * @return Customer|null
     */
    public function find(mixed $id, LockMode|int|null $lockMode = null, int|null $lockVersion = null): object|null;
}
