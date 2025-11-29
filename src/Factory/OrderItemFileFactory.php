<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Factory;

use Sylius\Component\Core\Model\OrderItemInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFile;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;

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
        array $configuration = []
    ): DigitalProductOrderItemFileInterface {
        $orderItemFile = $this->createNew();
        $orderItemFile->setName($name);
        $orderItemFile->setType($type);
        $orderItemFile->setDownloadLimit($downloadLimit);
        $orderItemFile->setConfiguration($configuration);
        $orderItemFile->setOrderItem($orderItem);

        return $orderItemFile;
    }
}
