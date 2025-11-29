<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Repository;

use Sylius\Component\User\Model\UserInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;

interface DigitalProductOrderItemFileRepositoryInterface
{
    public function findOneByUuidAndUser(string $uuid, UserInterface $user): ?DigitalProductOrderItemFileInterface;
}
