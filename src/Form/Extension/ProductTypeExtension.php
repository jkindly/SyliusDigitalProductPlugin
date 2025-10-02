<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Extension;

use Sylius\Bundle\AdminBundle\Form\Type\AddButtonType;
use Sylius\Bundle\AdminBundle\Form\Type\ProductType;
use SyliusDigitalProductPlugin\EventSubscriber\ProductDigitalFilesSubscriber;
use SyliusDigitalProductPlugin\Form\Type\DigitalProductFileType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

final class ProductTypeExtension extends AbstractTypeExtension
{
    private array $digitalProductTypes = [
        'uploaded_file' => 'uploaded_file',
        'external_url' => 'external_url',
    ];

    public function __construct(private readonly ProductDigitalFilesSubscriber $digitalFilesSubscriber)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('digitalFiles', LiveCollectionType::class, [
                'entry_type' => DigitalProductFileType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'button_add_type' => AddButtonType::class,
                'button_add_options' => [
                    'label' => 'sylius.ui.add_scope',
                    'types' => $this->digitalProductTypes,
                ],
                'button_delete_options' => [
                    'label' => false,
                ],
            ])
        ;

//        $builder->addEventSubscriber($this->digitalFilesSubscriber);
    }

    public static function getExtendedTypes(): iterable
    {
        yield ProductType::class;
    }
}
