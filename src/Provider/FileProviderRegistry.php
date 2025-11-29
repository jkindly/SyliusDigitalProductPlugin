<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

final class FileProviderRegistry implements FileProviderRegistryInterface
{
    /** @var array<string, FileProviderInterface> */
    private array $providers = [];

    /**
     * @param iterable<FileProviderInterface> $providers
     */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->getType()] = $provider;
        }
    }

    public function get(string $type): FileProviderInterface
    {
        if (false === array_key_exists($type, $this->providers)) {
            throw new \InvalidArgumentException(sprintf('Digital file provider with type "%s" not found.', $type));
        }

        return $this->providers[$type];
    }

    public function getAll(): array
    {
        return $this->providers;
    }
}
