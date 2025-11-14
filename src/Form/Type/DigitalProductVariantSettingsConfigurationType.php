<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettingsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DigitalProductVariantSettingsConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(DigitalProductVariantSettingsInterface::CONFIGURATION_HIDDEN_QUANTITY, CheckboxType::class, [
                'label' => 'sylius_digital_product.form.digital_product_settings.hidden_quantity',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $settings = $options['channel_settings'] ?? null;
            if (null === $data && $settings instanceof DigitalProductChannelSettingsInterface) {
                $data[DigitalProductVariantSettingsInterface::CONFIGURATION_HIDDEN_QUANTITY] = $settings->isHiddenQuantity();
                $event->setData($data);
            }
        });
  }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('channel_settings')
            ->setAllowedTypes('channel_settings', [DigitalProductChannelSettingsInterface::class, 'null'])
        ;
    }
}
