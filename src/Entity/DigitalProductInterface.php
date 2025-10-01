<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;

interface DigitalProductInterface
{
    /**
     * @return Collection<int, DigitalFileInterface>
     */
    public function getDigitalFiles(): Collection;

    public function addDigitalFile(DigitalFileInterface $file): void;

    public function removeDigitalFile(DigitalFileInterface $file): void;
}
