<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantDigitalProductSettingsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class DigitalProductChannelSettingsType extends DigitalProductSettingsType
{
    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_channel_settings';
    }
}
