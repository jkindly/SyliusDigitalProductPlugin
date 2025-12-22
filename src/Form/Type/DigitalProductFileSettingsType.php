<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class DigitalProductFileSettingsType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('downloadLimit', IntegerType::class, [
                'label' => 'sylius_digital_product.ui.uploaded_file.download_limit_per_customer',
                'help' => 'sylius_digital_product.ui.uploaded_file.download_limit_per_customer_help',
                'required' => false,
            ])
            ->add('daysAvailable', IntegerType::class, [
                'label' => 'sylius_digital_product.form.digital_product_settings.days_available',
                'help' => 'sylius_digital_product.form.digital_product_settings.days_available_help',
                'required' => false,
            ])
        ;
    }
}
