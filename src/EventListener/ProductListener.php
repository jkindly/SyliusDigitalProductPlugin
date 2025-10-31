<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductInterface;
use SyliusDigitalProductPlugin\Handler\DigitalFileHandlerInterface;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final readonly class ProductListener
{
    public function __construct(
        private DigitalFileHandlerInterface $uploadedDigitalFileHandler,
        private DigitalFileConfigurationSerializerInterface $uploadedDigitalFileSerializer,
        private EntityManagerInterface $entityManager,
        private string $uploadedDigitalFileType,
    ) {
    }

    public function handleUpload(GenericEvent $event): void
    {
        $product = $event->getSubject();
        Assert::isInstanceOf($product, DigitalProductInterface::class);

        foreach ($product->getDigitalFiles() as $digitalFile) {
            if ($this->uploadedDigitalFileType !== $digitalFile->getType()) {
                continue;
            }

            $configuration = $digitalFile->getConfiguration();
            $uploadedFile = $configuration['uploadedFile'] ?? null;
            if (null === $uploadedFile) {
                continue;
            }

            /** @var UploadedDigitalFileDto $dto */
            $dto = $this->uploadedDigitalFileSerializer->getDto($configuration);
            $dto->setUploadedFile($uploadedFile);
            $this->uploadedDigitalFileHandler->handle($dto);

            $digitalFile->setConfiguration($this->uploadedDigitalFileSerializer->getConfiguration($dto));
        }

        $this->entityManager->flush();
    }
}
