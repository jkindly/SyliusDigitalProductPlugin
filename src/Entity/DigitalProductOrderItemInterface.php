<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\OrderItemInterface;

interface DigitalProductOrderItemInterface extends OrderItemInterface, DigitalFilesInterface
{
}
