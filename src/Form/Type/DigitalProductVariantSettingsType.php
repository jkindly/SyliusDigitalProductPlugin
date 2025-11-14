<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettingsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DigitalProductVariantSettingsType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('configuration', ChannelBasedDigitalProductVariantSettingsType::class, [

            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_variant_settings';
    }
}
