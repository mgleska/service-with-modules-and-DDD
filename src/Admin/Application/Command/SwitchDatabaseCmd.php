<?php

declare(strict_types=1);

namespace App\Admin\Application\Command;

use App\CommonInfrastructure\DatabaseService;
use App\Customer\Domain\CustomerBag;
use Doctrine\DBAL\Exception as DBALException;

class SwitchDatabaseCmd
{
    public function __construct(
        private readonly DatabaseService $databaseService,
        private readonly CustomerBag $customerBag,
    ) {
    }

    /**
     * @throws DBALException
     */
    public function switchDatabase(): void
    {
        $this->databaseService->switchDatabase($this->customerBag->getDatabaseSuffix());
    }
}
