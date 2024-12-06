<?php

declare(strict_types=1);

namespace App\Customer\Application\Service;

use App\Auth\Domain\UserBag;
use App\Customer\Domain\CustomerBag;
use App\Customer\Domain\CustomerRepositoryInterface;

class BagService
{
    public function __construct(
        private readonly CustomerBag $customerBag,
        private readonly CustomerRepositoryInterface $repository,
        private readonly UserBag $userBag,
    ) {
    }

    public function fillBag(): void
    {
        $customer = $this->repository->find($this->userBag->getCustomerId());

        $this->customerBag
            ->setCustomerId($customer->getId())
            ->setDatabaseSuffix($customer->getDbNameSuffix())
            ->setName($customer->getName());
    }
}
