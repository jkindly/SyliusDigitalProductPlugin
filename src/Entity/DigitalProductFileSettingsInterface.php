<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Entity;

interface DigitalProductFileSettingsInterface
{
    public function getDownloadLimit(): ?int;

    public function setDownloadLimit(?int $downloadLimit): void;

    public function getDaysAvailable(): ?int;

    public function setDaysAvailable(?int $daysAvailable): void;
}
