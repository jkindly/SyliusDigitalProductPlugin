<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Extension;

use Sylius\Bundle\AdminBundle\Form\Type\AddButtonType;
use Sylius\Bundle\CoreBundle\Form\Type\ChannelCollectionType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Form\EventSubscriber\ChannelBasedFilesSubscriber;
use SyliusDigitalProductPlugin\Form\Type\DigitalProductFileType;
use SyliusDigitalProductPlugin\Provider\FileProviderRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;
use Webmozart\Assert\Assert;

final class ProductVariantTypeExtension extends AbstractTypeExtension
{
    private array $fileTypes = [];

    public function __construct(
        private readonly FileProviderRegistryInterface $registry,
        private readonly ChannelBasedFilesSubscriber $channelBasedFilesSubscriber,
    ) {
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
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $this->addFilesToForm($event);
        });

        $builder->addEventSubscriber($this->channelBasedFilesSubscriber);
    }

    public static function getExtendedTypes(): iterable
    {
        yield ProductVariantType::class;
    }

    private function addFilesToForm(FormEvent $event): void
    {
        $form = $event->getForm();
        $variant = $event->getData();
        Assert::isInstanceOf($variant, DigitalProductVariantInterface::class);

        $groupedFiles = $this->groupFilesByChannel($variant);

        $form->add('files', ChannelCollectionType::class, [
            'entry_type' => LiveCollectionType::class,
            'entry_options' => fn (DigitalProductChannelInterface $channel) => [
                'entry_type' => DigitalProductFileType::class,
                'entry_options' => [
                    'channel_settings' => $channel->getDigitalProductFileChannelSettings(),
                ],
                'label' => $channel->getName(),
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
                'data' => $groupedFiles[$channel->getCode()] ?? [],
                'constraints' => [
                    new Valid(),
                ],
            ],
            'constraints' => [
                new Valid(),
            ],
        ]);
    }

    private function groupFilesByChannel(DigitalProductVariantInterface $variant): array
    {
        $grouped = [];

        foreach ($variant->getFiles() as $file) {
            $channel = $file->getChannel();
            Assert::isInstanceOf($channel, DigitalProductChannelInterface::class);

            $channelCode = $channel->getCode();
            Assert::notNull($channelCode);

            if (!isset($grouped[$channelCode])) {
                $grouped[$channelCode] = [];
            }

            $grouped[$channelCode][] = $file;
        }

        return $grouped;
    }
}
