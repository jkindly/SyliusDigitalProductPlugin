<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity\Trait;

trait DigitalProductVariantAwareTrait
{
    protected bool $isDigital = false;

    public function isDigital(): bool
    {
        return $this->isDigital;
    }

    public function setIsDigital(bool $isDigital): void
    {
        $this->isDigital = $isDigital;
    }
}
