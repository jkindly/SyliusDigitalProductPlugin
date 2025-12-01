<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Extension;

use Sylius\Bundle\AdminBundle\Form\Type\ChannelType;
use SyliusDigitalProductPlugin\Form\Type\DigitalProductFileChannelSettingsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ChannelTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('digitalProductFileChannelSettings', DigitalProductFileChannelSettingsType::class, [
            'label' => false,
            'required' => false,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        yield ChannelType::class;
    }
}
