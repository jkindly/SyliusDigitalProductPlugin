<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Repository;

use Sylius\Component\User\Model\UserInterface;
use SyliusDigitalProductPlugin\Entity\OrderItemFileInterface;

interface OrderItemFileRepositoryInterface
{
    public function findOneByUuidAndUser(string $uuid, UserInterface $user): ?OrderItemFileInterface;
}
