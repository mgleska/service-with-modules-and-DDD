<?php

declare(strict_types=1);

namespace App\Admin\Application\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;

class InitCommonDbCmd
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws DBALException
     */
    public function initCommonDb(): void
    {
        $this->ex('SET FOREIGN_KEY_CHECKS=0');

        $this->ex('TRUNCATE TABLE cst_customer');
        $this->ex('TRUNCATE TABLE auth_user');

        $this->ex('SET FOREIGN_KEY_CHECKS=1');

        $this->ex("INSERT INTO cst_customer (id, version, name, db_name_suffix) VALUES(1, 1, 'System owner', '')");
        $this->ex("INSERT INTO cst_customer (id, version, name, db_name_suffix) VALUES(2, 1, 'Acme Company', 'acme')");
        $this->ex("INSERT INTO cst_customer (id, version, name, db_name_suffix) VALUES(3, 1, 'Foo-Bar Company', 'foo-bar')");

        $this->ex("INSERT INTO auth_user (id, login, customer_id, version, roles) VALUES(NULL, 'admin', 1, 1, JSON_ARRAY(\"ROLE_ADMIN\", \"ROLE_USER\"))");
        $this->ex("INSERT INTO auth_user (id, login, customer_id, version, roles) VALUES(NULL, 'user-1', 2, 1, JSON_ARRAY(\"ROLE_USER\"))");
        $this->ex("INSERT INTO auth_user (id, login, customer_id, version, roles) VALUES(NULL, 'user-2', 3, 1, JSON_ARRAY(\"ROLE_USER\"))");
    }

    /**
     * @throws DBALException
     */
    private function ex(string $q): void
    {
        $this->connection->executeStatement($q);
    }
}
