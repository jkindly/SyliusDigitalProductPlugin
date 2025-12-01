<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;

class DigitalProductChannelSettings extends AbstractDigitalProductSettings implements DigitalProductChannelSettingsInterface
{
    protected ?int $id = null;

    protected ?ChannelInterface $channel = null;

    protected bool $hiddenQuantity = false;

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

    public function isHiddenQuantity(): bool
    {
        return $this->hiddenQuantity;
    }

    public function setHiddenQuantity(bool $hiddenQuantity): void
    {
        $this->hiddenQuantity = $hiddenQuantity;
    }
}
