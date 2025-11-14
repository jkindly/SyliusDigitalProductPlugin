<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity\Trait;

use Sylius\Component\Core\Model\ChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettingsInterface;

trait DigitalProductVariantSettingsAwareTrait
{
    protected ?DigitalProductVariantSettingsInterface $digitalProductVariantSettings = null;

    public function getDigitalProductVariantSettings(): ?DigitalProductVariantSettingsInterface
    {
        return $this->digitalProductVariantSettings;
    }

    public function setDigitalProductVariantSettings(?DigitalProductVariantSettingsInterface $digitalProductVariantSettings): void
    {
        $this->digitalProductVariantSettings = $digitalProductVariantSettings;

        if (null !== $digitalProductVariantSettings && $digitalProductVariantSettings->getProductVariant() !== $this) {
            $digitalProductVariantSettings->setProductVariant($this);
        }
    }

    public function getConfigurationForChannel(ChannelInterface $channel): ?array
    {
        return $this->digitalProductVariantSettings?->getConfiguration()[$channel->getCode()] ?? null;
    }
}
