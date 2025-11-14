<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel as BaseChannel;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductChannelSettingsAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel implements DigitalProductChannelInterface
{
    use DigitalProductChannelSettingsAwareTrait;

    #[ORM\OneToOne(targetEntity: DigitalProductChannelSettings::class, mappedBy: 'channel', cascade: ['persist', 'remove'])]
    protected ?DigitalProductChannelSettingsInterface $digitalProductChannelSettings = null;
}
