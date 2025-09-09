<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

final class DigitalProductFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'sylius.form.digital_product_file.file',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_file';
    }
}
