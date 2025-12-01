<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ProductVariantInterface;

interface DigitalProductVariantInterface extends DigitalProductFilesInterface, ProductVariantInterface
{
    public function hasAnyFile(): bool;

    public function getDigitalProductVariantSettings(): ?DigitalProductVariantSettingsInterface;

    public function setDigitalProductVariantSettings(?DigitalProductVariantSettingsInterface $digitalProductVariantSettings): void;
}
