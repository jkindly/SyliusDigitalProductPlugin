<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;

class DigitalProductChannelSettings implements DigitalProductChannelSettingsInterface
{
    protected ?int $id = null;

    protected ?ChannelInterface $channel = null;

    protected array $configuration = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function setChannel(?ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getDownloadLimit(): ?int
    {
        return $this->configuration[self::CONFIGURATION_DOWNLOAD_LIMIT] ?? null;
    }

    public function isHiddenQuantity(): bool
    {
        return $this->configuration[DigitalProductVariantSettingsInterface::CONFIGURATION_HIDDEN_QUANTITY] ?? false;
    }
}
