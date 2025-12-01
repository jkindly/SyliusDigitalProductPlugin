<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Model\ResourceInterface;
use Sylius\Resource\Model\ToggleableInterface;

class DigitalProductVariantSettings implements
    DigitalProductVariantSettingsInterface,
    DigitalProductVariantAwareInterface,
    ResourceInterface,
    ToggleableInterface
{
    protected ?int $id = null;

    protected ?DigitalProductVariantInterface $productVariant = null;

    protected ?bool $enabled = false;

    protected bool $hiddenQuantity = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductVariant(): ?DigitalProductVariantInterface
    {
        return $this->productVariant;
    }

    public function setProductVariant(?DigitalProductVariantInterface $productVariant): void
    {
        $this->productVariant = $productVariant;

        if (null !== $productVariant && $productVariant->getDigitalProductVariantSettings() !== $this) {
            $productVariant->setDigitalProductVariantSettings($this);
        }
    }

    public function isHiddenQuantity(): bool
    {
        return $this->hiddenQuantity;
    }

    public function setHiddenQuantity(bool $hiddenQuantity): void
    {
        $this->hiddenQuantity = $hiddenQuantity;
    }

    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }
}
