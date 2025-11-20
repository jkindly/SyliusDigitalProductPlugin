<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Factory;

use Sylius\Component\Core\Model\OrderItemInterface;
use SyliusDigitalProductPlugin\Entity\OrderItemFile;
use SyliusDigitalProductPlugin\Entity\OrderItemFileInterface;

final class OrderItemFileFactory implements OrderItemFileFactoryInterface
{
    public function createNew(): OrderItemFileInterface
    {
        return new OrderItemFile();
    }

    public function createWithData(
        OrderItemInterface $orderItem,
        ?string $name = null,
        ?string $type = null,
        ?int $downloadLimit = null,
        array $configuration = []
    ): OrderItemFileInterface {
        $orderItemFile = $this->createNew();
        $orderItemFile->setName($name);
        $orderItemFile->setType($type);
        $orderItemFile->setDownloadLimit($downloadLimit);
        $orderItemFile->setConfiguration($configuration);
        $orderItemFile->setOrderItem($orderItem);

        return $orderItemFile;
    }
}
