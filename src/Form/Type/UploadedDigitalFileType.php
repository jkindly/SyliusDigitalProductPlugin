<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use SyliusDigitalProductPlugin\Entity\UploadedDigitalFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UploadedDigitalFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'sylius_digital_product.ui.name',
                'required' => false,
            ])
            ->add('uploadedFile', FileType::class, [
                'label' => 'sylius_digital_product.ui.uploaded_file',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UploadedDigitalFile::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_uploaded_file';
    }
}
