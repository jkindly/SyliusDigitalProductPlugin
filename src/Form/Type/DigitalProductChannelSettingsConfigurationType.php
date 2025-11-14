<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettingsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class DigitalProductChannelSettingsConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uploadedFileDownloadLimit', IntegerType::class, [
                'label' => 'sylius_digital_product.form.digital_product_settings.download_limit_per_customer',
                'required' => false,
            ])
            ->add(DigitalProductVariantSettingsInterface::CONFIGURATION_HIDDEN_QUANTITY, CheckboxType::class, [
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
