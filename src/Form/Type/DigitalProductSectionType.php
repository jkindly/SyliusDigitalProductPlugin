<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Sylius\Bundle\AdminBundle\Form\Type\AddButtonType;
use SyliusDigitalProductPlugin\EventSubscriber\ProductDigitalFilesSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class DigitalProductSectionType extends AbstractType
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
            ->add('files', LiveCollectionType::class, [
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

        $builder->addEventSubscriber($this->digitalFilesSubscriber);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_section';
    }
}
