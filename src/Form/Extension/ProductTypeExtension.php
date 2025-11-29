<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Extension;

use Sylius\Bundle\AdminBundle\Form\Type\AddButtonType;
use Sylius\Bundle\AdminBundle\Form\Type\ProductType;
use SyliusDigitalProductPlugin\Form\Type\DigitalProductFileType;
use SyliusDigitalProductPlugin\Provider\FileProviderRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

final class ProductTypeExtension extends AbstractTypeExtension
{
    private array $fileTypes = [];

    public function __construct(private readonly FileProviderRegistryInterface $registry)
    {
        foreach ($this->registry->getAll() as $key => $provider) {
            $this->fileTypes[$key] = $provider->getLabel();
        }
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
                    'label' => 'sylius_digital_product.ui.add_digital_file',
                    'types' => $this->fileTypes,
                ],
                'button_delete_options' => [
                    'label' => false,
                ],
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ProductType::class;
    }
}
