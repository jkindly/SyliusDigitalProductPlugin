<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;

interface DigitalProductVariantInterface
{
    public function isDigital(): bool;

    /**
     * @return Collection<int, DigitalFileInterface>
     */
    public function getDigitalFiles(): Collection;

    public function addDigitalFile(DigitalFileInterface $file): void;

    public function removeDigitalFile(DigitalFileInterface $file): void;

    public function getDigitalProductVariantSettings(): ?DigitalProductVariantSettingsInterface;

    public function setDigitalProductVariantSettings(?DigitalProductVariantSettingsInterface $settings): void;
}
