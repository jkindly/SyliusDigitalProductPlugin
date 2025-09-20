<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Synchronizer;

use Sylius\Component\Product\Model\ProductInterface;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;

interface DigitalFileSynchronizerInterface
{
    /**
     * @param array<DigitalFileInterface> $submittedFiles
     */
    public function sync(ProductInterface $product, array $submittedFiles, bool $flush = true): void;
}
