<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface DigitalProductVariantInterface extends DigitalProductFilesInterface, ProductVariantInterface
{
    public function isDigital(): bool;
}
