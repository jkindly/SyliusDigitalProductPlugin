<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Factory;

use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFile;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

final class OrderItemFileFactory implements OrderItemFileFactoryInterface
{
    public function createNew(): DigitalProductOrderItemFileInterface
    {
        return new DigitalProductOrderItemFile();
    }

    public function createWithData(
        OrderItemInterface $orderItem,
        ?string $name = null,
        ?string $type = null,
        ?int $downloadLimit = null,
        ?\DateTimeInterface $availableUntil = null,
        array $configuration = [],
    ): DigitalProductOrderItemFileInterface {
        $orderItemFile = $this->createNew();
        $orderItemFile->setName($name);
        $orderItemFile->setType($type);
        $orderItemFile->setDownloadLimit($downloadLimit);
        $orderItemFile->setAvailableUntil($availableUntil);
        $orderItemFile->setConfiguration($configuration);
        $orderItemFile->setOrderItem($orderItem);

        return $orderItemFile;
    }
}
