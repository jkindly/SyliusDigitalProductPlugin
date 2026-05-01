<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Factory;

use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

interface OrderItemFileFactoryInterface
{
    public function createNew(): DigitalProductOrderItemFileInterface;

    public function createWithData(
        OrderItemInterface $orderItem,
        ?string $name = null,
        ?string $type = null,
        ?int $downloadLimit = null,
        ?\DateTimeInterface $availableUntil = null,
        array $configuration = [],
    ): DigitalProductOrderItemFileInterface;
}
