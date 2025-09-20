<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventSubscriber;

use Sylius\Component\Product\Model\ProductInterface;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Synchronizer\DigitalFileSynchronizerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final readonly class ProductDigitalFilesSubscriber implements EventSubscriberInterface
{
    public function __construct(private DigitalFileSynchronizerInterface $synchronizer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $parent = $form->getParent();
        if ($parent === null) {
            return;
        }

        $product = $parent->getData();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$form->has('files')) {
            return;
        }

        /** @var array<DigitalFileInterface> $submittedFiles */
        $submittedFiles = $form->get('files')->getData() ?? [];
        $uploadedFiles = [];
        foreach ($submittedFiles as $file) {
            if ($file instanceof DigitalFileInterface) {
                $uploadedFiles[] = $file;
            }
        }

        if (count($uploadedFiles) > 0) {
            $this->synchronizer->sync($product, $uploadedFiles);
        }
    }
}
