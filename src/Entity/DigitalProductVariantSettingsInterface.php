<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

interface DigitalProductVariantSettingsInterface extends DigitalProductFileSettingsInterface
{
    public function isHiddenQuantity(): bool;

    public function setHiddenQuantity(bool $hiddenQuantity): void;
}
