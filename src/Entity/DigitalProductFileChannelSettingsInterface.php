<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;

interface DigitalProductFileChannelSettingsInterface extends DigitalProductFileSettingsInterface
{
    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;
}
