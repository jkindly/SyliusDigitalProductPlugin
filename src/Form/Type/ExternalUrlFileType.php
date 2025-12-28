<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use SyliusDigitalProductPlugin\Dto\ExternalUrlFileDto;
use SyliusDigitalProductPlugin\Validator\FileDtoValidatorInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class ExternalUrlFileType extends AbstractFileType
{
    /**
     * @param array<string> $validationGroups
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
            ->add('url', UrlType::class, [
                'label' => 'sylius_digital_product.ui.url',
                'default_protocol' => 'https',
                'attr' => [
                    'placeholder' => 'sylius_digital_product.ui.external_url.placeholder',
                ],
            ])
        ;

        parent::buildForm($builder, $options);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_external_url_file';
    }
}
