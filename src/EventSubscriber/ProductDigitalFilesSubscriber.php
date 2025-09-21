<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventSubscriber;

use Sylius\Component\Product\Model\ProductInterface;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Synchronizer\DigitalFileSynchronizerInterface;
use SyliusDigitalProductPlugin\Synchronizer\DigitalFileSynchronizerRegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final readonly class ProductDigitalFilesSubscriber implements EventSubscriberInterface
{
    public function __construct(private DigitalFileSynchronizerRegistryInterface $registry)
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
        $product = $form->getParent()?->getData();
        if (!$product instanceof ProductInterface) {
            return;
        }
dump($form->get('files')->getData());
        foreach ($form->get('files')->getData() ?? [] as $file) {
            $sync = $this->registry->getForType($file['type']);
            $sync->sync($product, $file['configuration'], false);
        }
    }
}
