<?php

declare(strict_types=1);

namespace App\CommonInfrastructure;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

use function preg_match;

class DatabaseService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws DBALException
     * @throws RuntimeException
     */
    public function switchDatabase(string $suffix): void
    {
        $suffix = trim($suffix);
        if ($suffix === '') {
            return;
        }

        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $suffix)) {
            throw new RuntimeException('Invalid suffix for tenant database: ' . $suffix);
        }

        $this->entityManager->clear();

        $conn = $this->entityManager->getConnection();
        $dbName = $_ENV['DB_DATABASE'] . '_' . $suffix;

        $sql = "USE `$dbName`";
        $conn->executeStatement($sql);
    }
}
