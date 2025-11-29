<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel as BaseChannel;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileChannelSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFileChannelSettingsAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel implements DigitalProductChannelInterface
{
    use DigitalProductFileChannelSettingsAwareTrait;

    #[ORM\OneToOne(targetEntity: DigitalProductFileChannelSettings::class, mappedBy: 'channel', cascade: ['persist', 'remove'])]
    protected ?DigitalProductFileChannelSettingsInterface $digitalProductFileChannelSettings = null;
}
