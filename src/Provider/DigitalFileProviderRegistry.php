<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

final class DigitalFileProviderRegistry implements DigitalFileProviderRegistryInterface
{
    /** @var array<string, DigitalFileProviderInterface> */
    private array $providers = [];

    /**
     * @param iterable<DigitalFileProviderInterface> $providers
     */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->getType()] = $provider;
        }
    }

    public function get(string $type): DigitalFileProviderInterface
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
