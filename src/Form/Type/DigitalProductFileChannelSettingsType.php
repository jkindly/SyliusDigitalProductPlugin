<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

class DigitalProductFileChannelSettingsType extends DigitalProductFileSettingsType
{
    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_channel_settings';
    }
}
