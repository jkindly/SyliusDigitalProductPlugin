<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Validator\DigitalFileDtoValidatorInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class UploadedDigitalFileType extends BaseDigitalFileType
{
    /**
     * @param DataTransformerInterface<array, UploadedDigitalFileDto> $dataTransformer
     */
    public function __construct(
        DataTransformerInterface $dataTransformer,
        DigitalFileDtoValidatorInterface $dtoValidator,
        string $dataClass,
        array $validationGroups = [],
    ) {
        parent::__construct($dataTransformer, $dtoValidator, $dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'sylius_digital_product.ui.name',
                'required' => false,
            ])
            ->add('uploadedFile', FileType::class, [
                'label' => 'sylius_digital_product.ui.file',
            ])
            ->add('size', HiddenType::class)
        ;

        parent::buildForm($builder, $options);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_uploaded_file';
    }
}
