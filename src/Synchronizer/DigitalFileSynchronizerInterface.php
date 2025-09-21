<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Synchronizer;

use Sylius\Component\Product\Model\ProductInterface;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;

interface DigitalFileSynchronizerInterface
{
    public function sync(ProductInterface $product, DigitalFileInterface $submittedFile, bool $flush = true): void;

    public function supports(string $type): bool;
}
