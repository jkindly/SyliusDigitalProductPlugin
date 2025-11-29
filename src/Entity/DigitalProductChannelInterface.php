<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;

interface DigitalProductChannelInterface extends ChannelInterface
{
    public function getDigitalProductFileChannelSettings(): ?DigitalProductFileChannelSettingsInterface;

    public function setDigitalProductFileChannelSettings(?DigitalProductFileChannelSettingsInterface $digitalProductChannelSettings): void;
}
