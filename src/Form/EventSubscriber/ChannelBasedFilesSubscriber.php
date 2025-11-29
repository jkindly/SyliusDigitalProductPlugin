<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
        Assert::isInstanceOf($groupedFiles, Collection::class);

        $flattenedFiles = $this->flattenChannelGroupedFiles($groupedFiles, $variant);

        $variant->setFiles(new ArrayCollection($flattenedFiles));
    }

    /**
     * @param Collection<string, Collection<int, DigitalProductFileInterface>> $groupedFiles
     * @return DigitalProductFileInterface[]
     */
    private function flattenChannelGroupedFiles(Collection $groupedFiles, DigitalProductVariantInterface $variant): array
    {
        /** @var ChannelInterface[] $channels */
        $channels = $this->channelRepository->findAll();
        $channelsByCode = [];

        foreach ($channels as $channel) {
            $channelCode = $channel->getCode();
            Assert::notNull($channelCode);
            $channelsByCode[$channelCode] = $channel;
        }

        $flattened = [];

        foreach ($groupedFiles as $channelCode => $files) {
            if (!isset($channelsByCode[$channelCode])) {
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
