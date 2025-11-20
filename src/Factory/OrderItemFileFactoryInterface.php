<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Factory;

use Sylius\Component\Core\Model\OrderItemInterface;
use SyliusDigitalProductPlugin\Entity\OrderItemFileInterface;

interface OrderItemFileFactoryInterface
{
    public function createNew(): OrderItemFileInterface;

    public function createWithData(
        OrderItemInterface $orderItem,
        ?string $name = null,
        ?string $type = null,
        ?int $downloadLimit = null,
        array $configuration = [],
    ): OrderItemFileInterface;
}
