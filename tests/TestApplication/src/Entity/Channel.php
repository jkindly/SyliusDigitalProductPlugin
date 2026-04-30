<?php

declare(strict_types=1);

namespace Tests\Jkindly\SyliusDigitalProductPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel as BaseChannel;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettings;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use Jkindly\SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFileChannelSettingsAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel implements DigitalProductChannelInterface
{
    use DigitalProductFileChannelSettingsAwareTrait;

    #[ORM\OneToOne(targetEntity: DigitalProductChannelSettings::class, mappedBy: 'channel', cascade: ['persist', 'remove'])]
    protected ?DigitalProductChannelSettingsInterface $digitalProductFileChannelSettings = null;
}
