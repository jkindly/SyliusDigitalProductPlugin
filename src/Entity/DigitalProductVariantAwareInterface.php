<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Entity;

interface DigitalProductVariantAwareInterface
{
    public function getProductVariant(): ?DigitalProductVariantInterface;

    public function setProductVariant(?DigitalProductVariantInterface $productVariant): void;
}
