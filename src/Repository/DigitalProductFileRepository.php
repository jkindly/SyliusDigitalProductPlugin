<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;

/**
 * @extends ServiceEntityRepository<DigitalProductFileInterface>
 */
class DigitalProductFileRepository extends ServiceEntityRepository implements DigitalProductFileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }
}
