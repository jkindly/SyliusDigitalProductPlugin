<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use SyliusDigitalProductPlugin\Entity\DigitalProductFileChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantDigitalProductSettingsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class DigitalProductChannelSettingsConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(SettingsInterface::CONFIGURATION_DOWNLOAD_LIMIT, IntegerType::class, [
                'label' => 'sylius_digital_product.form.digital_product_settings.download_limit_per_customer',
                'required' => false,
            ])
            ->add(SettingsInterface::CONFIGURATION_DAYS_AVAILABLE, IntegerType::class, [
                'label' => 'sylius_digital_product.form.digital_product_settings.days_available',
                'required' => false,
            ])
            ->add(DigitalProductVariantDigitalProductSettingsInterface::CONFIGURATION_HIDDEN_QUANTITY, CheckboxType::class, [
                'label' => 'sylius_digital_product.form.digital_product_settings.hidden_quantity',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_channel_settings_configuration';
    }
}
