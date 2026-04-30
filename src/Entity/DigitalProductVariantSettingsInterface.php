<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Entity;

interface DigitalProductVariantSettingsInterface
{
    public function isHiddenQuantity(): bool;

    public function setHiddenQuantity(bool $hiddenQuantity): void;
}
