<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UploadedDigitalFileRepository extends ServiceEntityRepository implements UploadedDigitalFileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }
}
