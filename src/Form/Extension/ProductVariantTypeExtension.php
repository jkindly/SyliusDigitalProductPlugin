<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Extension;

use Sylius\Bundle\AdminBundle\Form\Type\AddButtonType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use SyliusDigitalProductPlugin\Form\Type\ChannelBasedDigitalProductVariantSettingsType;
use SyliusDigitalProductPlugin\Form\Type\DigitalProductFileType;
use SyliusDigitalProductPlugin\Form\Type\DigitalProductVariantSettingsType;
use SyliusDigitalProductPlugin\Provider\DigitalFileProviderRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

final class ProductVariantTypeExtension extends AbstractTypeExtension
{
    private array $fileTypes = [];

    public function __construct(private readonly DigitalFileProviderRegistryInterface $registry)
    {
        foreach ($this->registry->getAll() as $key => $provider) {
            $this->fileTypes[$key] = $provider->getLabel();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isDigital', null, [
                'required' => false,
                'label' => false,
            ])
            ->add('digitalProductVariantSettings', DigitalProductVariantSettingsType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('digitalFiles', LiveCollectionType::class, [
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
        yield ProductVariantType::class;
    }
}
