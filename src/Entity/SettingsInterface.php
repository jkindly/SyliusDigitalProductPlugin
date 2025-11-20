<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

interface SettingsInterface
{
    public function getDownloadLimit(): ?int;

    public function isHiddenQuantity(): bool;
}
