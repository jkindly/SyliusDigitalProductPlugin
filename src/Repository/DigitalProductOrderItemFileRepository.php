<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\User\Model\UserInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;

/**
 * @extends ServiceEntityRepository<DigitalProductOrderItemFileInterface>
 */
class DigitalProductOrderItemFileRepository extends ServiceEntityRepository implements DigitalProductOrderItemFileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findOneByUuid(string $uuid): ?DigitalProductOrderItemFileInterface
    {
        /** @var DigitalProductOrderItemFileInterface|null */
        return $this->createQueryBuilder('oif')
            ->andWhere('oif.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByUuidAndUser(string $uuid, UserInterface $user): ?DigitalProductOrderItemFileInterface
    {
        /** @var DigitalProductOrderItemFileInterface|null */
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

    public function hasFilesForOrderItem(OrderItemInterface $orderItem): bool
    {
        return 0 < (int) $this->createQueryBuilder('oif')
            ->select('COUNT(oif.id)')
            ->andWhere('oif.orderItem = :orderItem')
            ->setParameter('orderItem', $orderItem)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findByOrder(OrderInterface $order): array
    {
        /** @var DigitalProductOrderItemFileInterface[] */
        return $this->createQueryBuilder('oif')
            ->join('oif.orderItem', 'oi')
            ->andWhere('oi.order = :order')
            ->setParameter('order', $order)
            ->getQuery()
            ->getResult()
        ;
    }
}
