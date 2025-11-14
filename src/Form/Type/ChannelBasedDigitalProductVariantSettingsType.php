<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Sylius\Bundle\CoreBundle\Form\Type\ChannelCollectionType;
use Sylius\Bundle\PromotionBundle\Form\Type\Action\FixedDiscountConfigurationType;
use Sylius\Component\Core\Model\ChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChannelBasedDigitalProductVariantSettingsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => DigitalProductVariantSettingsConfigurationType::class,
            'entry_options' => fn (DigitalProductChannelInterface $channel) => [
                'label' => $channel->getName(),
                'channel_settings' => $channel->getDigitalProductChannelSettings(),
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChannelCollectionType::class;
    }
}
