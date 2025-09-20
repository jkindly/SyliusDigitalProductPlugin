<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

interface DigitalFileHandlerRegistryInterface
{
    public function getHandlerForType(string $type): DigitalFileHandlerInterface;
}
