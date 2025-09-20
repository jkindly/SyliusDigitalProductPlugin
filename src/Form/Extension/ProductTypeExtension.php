<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Extension;

use Sylius\Bundle\AdminBundle\Form\Type\ProductType;
use SyliusDigitalProductPlugin\Form\Type\DigitalProductSectionType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('digitalProductSection', DigitalProductSectionType::class, [
                'label' => false,
                'required' => false,
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return yield ProductType::class;
    }
}
