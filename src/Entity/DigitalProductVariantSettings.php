<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariant;

class DigitalProductVariantSettings implements DigitalProductVariantSettingsInterface
{
    protected ?int $id = null;

    protected ?ProductVariant $productVariant = null;

    protected array $configuration = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductVariant(): ?ProductVariant
    {
        return $this->productVariant;
    }

    public function setProductVariant(?ProductVariant $productVariant): void
    {
        $this->productVariant = $productVariant;
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
        return $this->configuration[DigitalProductChannelSettingsInterface::CONFIGURATION_DOWNLOAD_LIMIT] ?? null;
    }

    public function isHiddenQuantity(): bool
    {
        return $this->configuration[self::CONFIGURATION_HIDDEN_QUANTITY] ?? false;
    }
}
