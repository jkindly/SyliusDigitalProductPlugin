<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

interface DigitalProductFileSettingsInterface
{
    public function getDownloadLimit(): ?int;

    public function setDownloadLimit(?int $downloadLimit): void;

    public function isHiddenQuantity(): bool;

    public function setHiddenQuantity(bool $hiddenQuantity): void;

    public function getDaysAvailable(): ?int;

    public function setDaysAvailable(?int $daysAvailable): void;
}
