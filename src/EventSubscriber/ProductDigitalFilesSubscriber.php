<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventSubscriber;

use Sylius\Component\Product\Model\ProductInterface;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Entity\UploadedDigitalFileInterface;
use SyliusDigitalProductPlugin\Repository\UploadedDigitalFileRepositoryInterface;
use SyliusDigitalProductPlugin\Synchronizer\DigitalFileSynchronizerInterface;
use SyliusDigitalProductPlugin\Synchronizer\DigitalFileSynchronizerRegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final readonly class ProductDigitalFilesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DigitalFileSynchronizerRegistryInterface $registry,
        private UploadedDigitalFileRepositoryInterface $uploadedDigitalFileRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
//            FormEvents::POST_SUBMIT => 'onPostSubmit',
//            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $product = $form->getParent()?->getData();
        if (!$product instanceof ProductInterface) {
            return;
        }

        /** @var array<int, array<string, mixed>> $files */
        $files = $form->get('files')->getData() ?? [];
        foreach ($files as $file) {
            if (!$file['configuration'] instanceof DigitalFileInterface) {
                continue;
            }

            /** @var string $type */
            $type = $file['type'];
            $sync = $this->registry->getForType($type);
            $sync->sync($product, $file['configuration'], false);
        }
    }

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $product = $form->getParent()?->getData();
        if (!$product instanceof ProductInterface) {
            return;
        }

        /** @var UploadedDigitalFileInterface[] $existingFilesForProduct */
        $existingFilesForProduct = $this->uploadedDigitalFileRepository->findBy(['product' => $product]);
        if (empty($existingFilesForProduct)) {
            return;
        }

        $files = array_map(function (UploadedDigitalFileInterface $uploadedDigitalFile) {
            return [
                'type' => $uploadedDigitalFile->getType(),
                'configuration' => $uploadedDigitalFile,
            ];
        }, $existingFilesForProduct);

//        $form->get('files')->setData($existingFilesForProduct);
    }
}
