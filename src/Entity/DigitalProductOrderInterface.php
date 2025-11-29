<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

interface DigitalProductOrderInterface extends OrderInterface
{
    public function hasAnyFile(): bool;
}
