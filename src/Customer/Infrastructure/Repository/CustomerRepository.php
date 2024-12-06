<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Repository;

use App\Customer\Domain\Customer;
use App\Customer\Domain\CustomerRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository implements CustomerRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function save(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $entity->incrementVersion();
            $this->getEntityManager()->flush();
        }
    }

    public function findById(int $id): Customer|null
    {
        $dbName = $_ENV['DB_DATABASE'];

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Customer::class, 'c');

        $sql = "SELECT " . $rsm->generateSelectClause() . " FROM `$dbName`.cst_customer c WHERE c.id = :id";

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('id', $id, ParameterType::INTEGER);
        $result = $query->getResult();
        $query->free();

        if (count($result) > 0) {
            return $result[0];
        }

        return null;
    }

    /**
     * @throws DBALException
     */
    public function checkId(int $id): bool
    {
        $dbName = $_ENV['DB_DATABASE'];

        $sql = "SELECT id FROM `$dbName`.cst_customer WHERE id = :id";

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, ParameterType::INTEGER);
        $result = $stmt->executeQuery();
        $count = $result->rowCount();
        $result->free();

        return $count > 0;
    }
}
