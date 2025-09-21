<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Synchronizer;

interface DigitalFileSynchronizerRegistryInterface
{
    public function getForType(string $type): DigitalFileSynchronizerInterface;
}
