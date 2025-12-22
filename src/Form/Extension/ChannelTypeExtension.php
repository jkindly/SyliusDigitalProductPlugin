<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Extension;

use Sylius\Bundle\AdminBundle\Form\Type\ChannelType;
use SyliusDigitalProductPlugin\Form\Type\DigitalProductChannelSettingsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class ChannelTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('digitalProductFileChannelSettings', DigitalProductChannelSettingsType::class, [
            'label' => false,
            'required' => false,
            'constraints' => [
                new Valid(),
            ],
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        yield ChannelType::class;
    }
}
