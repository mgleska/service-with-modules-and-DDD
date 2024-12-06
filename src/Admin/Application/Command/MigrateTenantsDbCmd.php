<?php

declare(strict_types=1);

namespace App\Admin\Application\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\PhpSubprocess;

use function array_diff;
use function explode;
use function join;
use function preg_match;

class MigrateTenantsDbCmd
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $projectDir,
    ) {
    }

    /**
     * @throws DBALException
     */
    public function migrateTenantsDb(SymfonyStyle $io): void
    {
        $dotenvVars = join(',', array_diff(explode(',', $_ENV['SYMFONY_DOTENV_VARS']), ['DB_DATABASE']));

        $commonDbName = $_ENV['DB_DATABASE'];

        $sql = 'SELECT db_name_suffix FROM cst_customer';
        $suffixes = $this->connection->fetchFirstColumn($sql);

        foreach ($suffixes as $suffix) {
            if ($suffix === '') {
                continue;
            }

            if (! preg_match('/^[a-zA-Z0-9_-]+$/', $suffix)) {
                throw new RuntimeException('Invalid suffix for tenant database: ' . $suffix);
            }

            $dbName = $commonDbName . '_' . $suffix;

            $io->title('Migrations for database: ' . $dbName);

            $sql = "CREATE DATABASE IF NOT EXISTS `$dbName`";
            $ret = $this->connection->executeStatement($sql);
            if ($ret) {
                $io->info('Database created');
            }

            $binConsole = $this->projectDir . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'console';
            $process = new PhpSubprocess(
                [$binConsole, '--no-ansi', 'doctrine:migration:migrate', '--no-interaction'],
                null,
                ['DB_DATABASE' => $dbName, 'SYMFONY_DOTENV_VARS' => $dotenvVars]
            );
            $process->setTimeout(300);
            if ($process->isTtySupported()) {
                $process->setTty(true);
            }
            $process->run();
            echo $process->getOutput();
            echo $process->getErrorOutput();
        }
    }
}
