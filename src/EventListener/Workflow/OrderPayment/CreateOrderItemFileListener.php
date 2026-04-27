<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener\Workflow\OrderPayment;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use SyliusDigitalProductPlugin\Copier\OrderItemFileCopierInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Factory\OrderItemFileFactoryInterface;
use SyliusDigitalProductPlugin\Repository\DigitalProductOrderItemFileRepositoryInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class CreateOrderItemFileListener
{
    public function __construct(
        private OrderItemFileFactoryInterface $orderItemFileFactory,
        private EntityManagerInterface $entityManager,
        private OrderItemFileCopierInterface $orderItemFileCopier,
        private DigitalProductOrderItemFileRepositoryInterface $orderItemFileRepository,
        private string $uploadedFileType,
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

            if ($this->orderItemFileRepository->hasFilesForOrderItem($item)) {
                continue;
            }

            $channelSettings = $channel->getDigitalProductFileChannelSettings();

            $files = $variant->getFiles();
            foreach ($files as $file) {
                $fileSettings = $file->getSettings();
                $daysAvailable = $fileSettings?->getDaysAvailable() ?? $channelSettings?->getDaysAvailable();
                $availableUntil = null !== $daysAvailable
                    ? (new \DateTimeImmutable())->modify(sprintf('+%d days', $daysAvailable))
                    : null;

                $configuration = $file->getConfiguration();
                if ($this->uploadedFileType === $file->getType()) {
                    $configuration = $this->orderItemFileCopier->copy($configuration);
                }

                $orderItemFile = $this->orderItemFileFactory->createWithData(
                    $item,
                    $file->getName(),
                    $file->getType(),
                    $fileSettings?->getDownloadLimit() ?? $channelSettings?->getDownloadLimit(),
                    false !== $availableUntil ? $availableUntil : null,
                    $configuration,
                );
                $this->entityManager->persist($orderItemFile);
            }
        }

        $this->entityManager->flush();
    }
}
