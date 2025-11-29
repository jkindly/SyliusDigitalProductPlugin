<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

interface DigitalProductFileAwareInterface
{
    public function getFile(): ?DigitalProductFileInterface;

    public function setFile(?DigitalProductFileInterface $file): void;
}
