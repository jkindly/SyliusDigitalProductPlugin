<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ProductVariantInterface;

interface DigitalProductVariantSettingsInterface
{
    public function isHiddenQuantity(): bool;

    public function setHiddenQuantity(bool $hiddenQuantity): void;
}
