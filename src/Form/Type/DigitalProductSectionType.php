<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class DigitalProductSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('files', LiveCollectionType::class, [
                'entry_type' => DigitalProductFileType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'sylius_digital_product.ui.files',
                'block_name' => 'entry',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_section';
    }
}
