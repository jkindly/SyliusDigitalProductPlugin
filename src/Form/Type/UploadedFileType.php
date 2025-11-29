<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Validator\FileDtoValidatorInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

final class UploadedFileType extends BaseFileType
{
    /**
     * @param DataTransformerInterface<array, UploadedFileDto> $dataTransformer
     */
    public function __construct(
        DataTransformerInterface $dataTransformer,
        FileDtoValidatorInterface $dtoValidator,
        string $dataClass,
        array $validationGroups = [],
    ) {
        parent::__construct($dataTransformer, $dtoValidator, $dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uploadedFile', FileType::class, [
                'label' => 'sylius_digital_product.ui.file',
            ])
        ;

        parent::buildForm($builder, $options);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_uploaded_file';
    }
}
