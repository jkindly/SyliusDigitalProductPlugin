<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Entity;

interface DigitalProductFileAwareInterface
{
    public function getFile(): ?DigitalProductFileInterface;

    public function setFile(?DigitalProductFileInterface $file): void;
}
