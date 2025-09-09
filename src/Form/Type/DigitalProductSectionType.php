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
//                'entry_options' => ['product' => $options['data']],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'sylius.form.digital_product.files',
                'block_name' => 'entry',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_section';
    }
}
