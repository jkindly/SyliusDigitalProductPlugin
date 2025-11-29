<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\OrderInterface;

interface DigitalProductOrderInterface extends OrderInterface
{
    public function hasAnyFile(): bool;
}
