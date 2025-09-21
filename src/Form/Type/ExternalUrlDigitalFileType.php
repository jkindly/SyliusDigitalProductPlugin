<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class ExternalUrlDigitalFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class, [
                'label' => 'sylius_digital_product.ui.external_url',
                'required' => true,
            ])
        ;
    }
}
