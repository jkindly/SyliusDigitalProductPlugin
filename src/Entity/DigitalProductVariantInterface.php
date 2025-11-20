<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface DigitalProductVariantInterface extends DigitalFilesInterface, ProductVariantInterface
{
    public function isDigital(): bool;

    public function getDigitalProductVariantSettings(): ?DigitalProductVariantSettingsInterface;

    public function setDigitalProductVariantSettings(?DigitalProductVariantSettingsInterface $settings): void;
}
