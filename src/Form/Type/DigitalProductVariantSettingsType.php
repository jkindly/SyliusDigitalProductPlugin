<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class DigitalProductVariantSettingsType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'block_prefix' => 'sylius_digital_product_override_channel_settings',
            ])
            ->add('hiddenQuantity', CheckboxType::class, [
                'label' => 'sylius_digital_product.form.digital_product_settings.hidden_quantity',
                'help' => 'sylius_digital_product.form.digital_product_settings.hidden_quantity_help',
                'required' => false,
            ])
        ;
    }
}
