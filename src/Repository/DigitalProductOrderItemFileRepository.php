<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\User\Model\UserInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;

class DigitalProductOrderItemFileRepository extends ServiceEntityRepository implements DigitalProductOrderItemFileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findOneByUuidAndUser(string $uuid, UserInterface $user): ?DigitalProductOrderItemFileInterface
    {
        return $this->createQueryBuilder('oif')
            ->join('oif.orderItem', 'oi')
            ->join('oi.order', 'o')
            ->join('o.customer', 'c')
            ->join('c.user', 'u')
            ->andWhere('oif.uuid = :uuid')
            ->andWhere('u = :user')
            ->setParameter('uuid', $uuid)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
