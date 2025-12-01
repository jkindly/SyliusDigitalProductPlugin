<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity\Trait;

use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;

trait DigitalProductFileChannelSettingsAwareTrait
{
    protected ?DigitalProductChannelSettingsInterface $digitalProductFileChannelSettings = null;

    public function getDigitalProductFileChannelSettings(): ?DigitalProductChannelSettingsInterface
    {
        return $this->digitalProductFileChannelSettings;
    }

    public function setDigitalProductFileChannelSettings(?DigitalProductChannelSettingsInterface $digitalProductFileChannelSettings): void
    {
        $this->digitalProductFileChannelSettings = $digitalProductFileChannelSettings;

        if (null !== $digitalProductFileChannelSettings && $digitalProductFileChannelSettings->getChannel() !== $this) {
            $digitalProductFileChannelSettings->setChannel($this);
        }
    }
}
