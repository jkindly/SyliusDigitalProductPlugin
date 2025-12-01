<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Resource\Model\ResourceInterface;

interface DigitalProductChannelSettingsInterface extends
    DigitalProductFileSettingsInterface,
    DigitalProductVariantSettingsInterface,
    ResourceInterface
{
    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;
}
