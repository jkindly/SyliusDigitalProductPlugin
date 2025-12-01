<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ProductVariantInterface;

interface DigitalProductVariantAwareInterface
{
    public function getProductVariant(): ?DigitalProductVariantInterface;

    public function setProductVariant(?DigitalProductVariantInterface $productVariant): void;
}
