<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

abstract class AbstractDigitalProductSettings
{
    protected ?int $downloadLimit = null;

    protected ?int $daysAvailable = null;

    public function getDownloadLimit(): ?int
    {
        return $this->downloadLimit;
    }

    public function setDownloadLimit(?int $downloadLimit): void
    {
        $this->downloadLimit = $downloadLimit;
    }

    public function getDaysAvailable(): ?int
    {
        return $this->daysAvailable;
    }

    public function setDaysAvailable(?int $daysAvailable): void
    {
        $this->daysAvailable = $daysAvailable;
    }
}
