<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Repository;

use App\Order\Domain\FixedAddress;
use App\Order\Domain\FixedAddressRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FixedAddress>
 */
class FixedAddressRepository extends ServiceEntityRepository implements FixedAddressRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FixedAddress::class);
    }

    public function save(FixedAddress $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $entity->incrementVersion();
            $this->getEntityManager()->flush();
        }
    }
}
