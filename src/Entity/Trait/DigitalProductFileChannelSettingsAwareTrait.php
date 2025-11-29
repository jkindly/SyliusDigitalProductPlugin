<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity\Trait;

use SyliusDigitalProductPlugin\Entity\DigitalProductFileChannelSettingsInterface;

trait DigitalProductFileChannelSettingsAwareTrait
{
    protected ?DigitalProductFileChannelSettingsInterface $digitalProductFileChannelSettings = null;

    public function getDigitalProductFileChannelSettings(): ?DigitalProductFileChannelSettingsInterface
    {
        return $this->digitalProductFileChannelSettings;
    }

    public function setDigitalProductFileChannelSettings(?DigitalProductFileChannelSettingsInterface $digitalProductFileChannelSettings): void
    {
        $this->digitalProductFileChannelSettings = $digitalProductFileChannelSettings;

        if (null !== $digitalProductFileChannelSettings && $digitalProductFileChannelSettings->getChannel() !== $this) {
            $digitalProductFileChannelSettings->setChannel($this);
        }
    }
}
