<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;

interface DigitalFileHandlerInterface
{
    public function supports(string $type): bool;

    public function process(DigitalFileInterface $digitalFile): void;
}
