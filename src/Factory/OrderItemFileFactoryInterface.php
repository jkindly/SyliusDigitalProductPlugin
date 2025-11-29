<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Factory;

use Sylius\Component\Core\Model\OrderItemInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;

interface OrderItemFileFactoryInterface
{
    public function createNew(): DigitalProductOrderItemFileInterface;

    public function createWithData(
        OrderItemInterface $orderItem,
        ?string $name = null,
        ?string $type = null,
        ?int $downloadLimit = null,
        array $configuration = [],
    ): DigitalProductOrderItemFileInterface;
}
