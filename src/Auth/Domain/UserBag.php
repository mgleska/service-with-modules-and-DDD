<?php

declare(strict_types=1);

namespace App\Auth\Domain;

class UserBag
{
    private int $userId;
    private int $customerId;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setCustomerId(int $customerId): void
    {
        $this->customerId = $customerId;
    }
}
