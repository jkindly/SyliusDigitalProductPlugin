<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;

interface DigitalProductChannelSettingsInterface extends SettingsInterface
{
    public const CONFIGURATION_DOWNLOAD_LIMIT = 'downloadLimit';

    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;

    public function getConfiguration(): array;

    public function setConfiguration(array $configuration): void;
}
