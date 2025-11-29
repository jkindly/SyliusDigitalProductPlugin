<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

interface FileProviderRegistryInterface
{
    public function get(string $type): FileProviderInterface;

    /**
     * @return array<string, FileProviderInterface>
     */
    public function getAll(): array;
}
