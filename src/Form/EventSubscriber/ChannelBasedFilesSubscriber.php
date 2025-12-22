<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\EventSubscriber;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Webmozart\Assert\Assert;

final readonly class ChannelBasedFilesSubscriber implements EventSubscriberInterface
{
    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onSubmit(FormEvent $event): void
    {
        $variant = $event->getData();
        Assert::isInstanceOf($variant, DigitalProductVariantInterface::class);

        $form = $event->getForm();
        $groupedFiles = $form->get('files')->getData();
        Assert::isArray($groupedFiles);

        $this->syncFilesFromGrouped($variant, $groupedFiles);
    }

    /**
     * @param array<string, mixed> $groupedFiles
     */
    private function syncFilesFromGrouped(DigitalProductVariantInterface $variant, array $groupedFiles): void
    {
        $submittedFilesById = [];
        $newFiles = [];

        $submittedFiles = $this->flattenChannelGroupedFiles($groupedFiles, $variant);
        foreach ($submittedFiles as $file) {
            if ($file->getId() !== null) {
                $submittedFilesById[$file->getId()] = $file;
            } else {
                $newFiles[] = $file;
            }
        }

        $currentFiles = $variant->getFiles()->toArray();
        foreach ($currentFiles as $existingFile) {
            if (!$existingFile instanceof DigitalProductFileInterface) {
                continue;
            }

            $existingFileId = $existingFile->getId();
            if (null === $existingFileId) {
                continue;
            }

            if (!array_key_exists($existingFileId, $submittedFilesById)) {
                $variant->removeFile($existingFile);

                continue;
            }

            $submittedVersion = $submittedFilesById[$existingFileId];
            if ($existingFile->getChannel() !== $submittedVersion->getChannel()) {
                $existingFile->setChannel($submittedVersion->getChannel());
            }
        }

        foreach ($newFiles as $newFile) {
            if (!$variant->getFiles()->contains($newFile)) {
                $variant->addFile($newFile);
            }
        }
    }

    /**
     * @param array<string, mixed> $groupedFiles
     *
     * @return DigitalProductFileInterface[]
     */
    private function flattenChannelGroupedFiles(array $groupedFiles, DigitalProductVariantInterface $variant): array
    {
        /** @var ChannelInterface[] $channels */
        $channels = $this->channelRepository->findAll();
        $channelsByCode = [];
        $flattened = [];

        foreach ($channels as $channel) {
            $channelsByCode[$channel->getCode()] = $channel;
        }

        foreach ($groupedFiles as $channelCode => $files) {
            if (!isset($channelsByCode[$channelCode]) || !is_iterable($files)) {
                continue;
            }

            $channel = $channelsByCode[$channelCode];

            foreach ($files as $file) {
                if (!$file instanceof DigitalProductFileInterface) {
                    continue;
                }

                $file->setChannel($channel);
                $file->setProductVariant($variant);
                $flattened[] = $file;
            }
        }

        return $flattened;
    }
}
