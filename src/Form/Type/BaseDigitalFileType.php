<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use SyliusDigitalProductPlugin\Validator\DigitalFileDtoValidatorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;

abstract class BaseDigitalFileType extends AbstractType
{
    public function __construct(
        protected readonly DataTransformerInterface $dataTransformer,
        protected readonly ?DigitalFileDtoValidatorInterface $dtoValidator,
        protected readonly string $dataClass,
        protected readonly array $validationGroups = [],
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->dataTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'validation_groups' => $this->validationGroups,
        ]);

        if (null !== $this->dtoValidator) {
            $resolver->setDefaults([
                'constraints' => [
                    new Callback([$this->dtoValidator, 'validate'], $this->validationGroups),
                ],
            ]);
        }
    }
}
