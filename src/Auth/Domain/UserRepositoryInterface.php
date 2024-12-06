<?php

declare(strict_types=1);

namespace App\Auth\Domain;

interface UserRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @return User|null
     */
    public function findOneBy(array $criteria, array|null $orderBy = null): object|null;
}
