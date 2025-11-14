<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity\Trait;

use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;

trait DigitalProductChannelSettingsAwareTrait
{
    protected ?DigitalProductChannelSettingsInterface $digitalProductChannelSettings = null;

    public function getDigitalProductChannelSettings(): ?DigitalProductChannelSettingsInterface
    {
        return $this->digitalProductChannelSettings;
    }

    public function setDigitalProductChannelSettings(?DigitalProductChannelSettingsInterface $digitalProductChannelSettings): void
    {
        $this->digitalProductChannelSettings = $digitalProductChannelSettings;

        if (null !== $digitalProductChannelSettings && $digitalProductChannelSettings->getChannel() !== $this) {
            $digitalProductChannelSettings->setChannel($this);
        }
    }
}
