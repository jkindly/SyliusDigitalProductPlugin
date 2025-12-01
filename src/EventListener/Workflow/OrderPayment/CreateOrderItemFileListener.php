<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener\Workflow\OrderPayment;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Factory\OrderItemFileFactoryInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class CreateOrderItemFileListener
{
    public function __construct(
        private OrderItemFileFactoryInterface $orderItemFileFactory,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $channel = $order->getChannel();
        Assert::isInstanceOf($channel, DigitalProductChannelInterface::class);

        foreach ($order->getItems() as $item) {
            /** @var DigitalProductVariantInterface $variant */
            $variant = $item->getVariant();
            if (!$variant->hasAnyFile()) {
                continue;
            }

            $variantSettings = $variant->getDigitalProductVariantSettings();
            $channelSettings = $channel->getDigitalProductFileChannelSettings();

            $files = $variant->getFiles();
            foreach ($files as $file) {
                $orderItemFile = $this->orderItemFileFactory->createWithData(
                    $item,
                    $file->getName(),
                    $file->getType(),
                    $variantSettings?->getDownloadLimit() ?? $channelSettings?->getDownloadLimit(),
                    $file->getConfiguration(),
                );
                $this->entityManager->persist($orderItemFile);
            }
        }
    }
}
