<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

interface DigitalFileProviderRegistryInterface
{
    public function get(string $type): DigitalFileProviderInterface;

    /**
     * @return array<string, DigitalFileProviderInterface>
     */
    public function getAll(): array;
}
