<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Factory\ChunkedUploadedFileFactoryInterface;
use SyliusDigitalProductPlugin\Handler\FileHandlerInterface;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerInterface;
use SyliusDigitalProductPlugin\Uploader\ChunkedUploadHandlerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final readonly class ProductVariantListener
{
    public function __construct(
        private FileHandlerInterface $uploadedFileHandler,
        private FileConfigurationSerializerInterface $uploadedFileSerializer,
        private EntityManagerInterface $entityManager,
        private string $uploadedFileType,
        private ChunkedUploadedFileFactoryInterface $chunkedUploadedFileFactory,
        private ChunkedUploadHandlerInterface $chunkedUploadHandler,
    ) {
    }

    public function handleProductVariantUpdate(GenericEvent $event): void
    {
        $productVariant = $event->getSubject();
        Assert::isInstanceOf($productVariant, DigitalProductVariantInterface::class);

        $this->handleUploadedFiles($productVariant->getFiles());
    }

    public function handleSimpleProductUpdate(GenericEvent $event): void
    {
        $product = $event->getSubject();
        Assert::isInstanceOf($product, ProductInterface::class);

        if (false === $product->isSimple()) {
            return;
        }

        $variant = $product->getVariants()->first();
        Assert::isInstanceOf($variant, DigitalProductVariantInterface::class);

        $this->handleUploadedFiles($variant->getFiles());
    }

    /**
     * @param Collection<int, DigitalProductFileInterface> $files
     */
    private function handleUploadedFiles(Collection $files): void
    {
        foreach ($files as $file) {
            if ($this->uploadedFileType !== $file->getType()) {
                continue;
            }

            $configuration = $file->getConfiguration();
            $uploadedFile = $configuration['uploadedFile'] ?? null;
            $chunkFileId = $configuration['chunkFileId'] ?? null;
            $chunkOriginalFilename = $configuration['chunkOriginalFilename'] ?? null;

            if (null === $uploadedFile && null === $chunkFileId) {
                continue;
            }

            /** @var UploadedFileDto $dto */
            $dto = $this->uploadedFileSerializer->getDto($configuration);

            if (null !== $chunkFileId && null !== $chunkOriginalFilename) {
                $dto->setUploadedFile($this->chunkedUploadedFileFactory->createFromChunk($chunkFileId, $chunkOriginalFilename));
            } elseif (null !== $uploadedFile) {
                $dto->setUploadedFile($uploadedFile);
            }

            $this->uploadedFileHandler->handle($dto);

            if (null !== $chunkFileId) {
                $this->chunkedUploadHandler->deleteChunks($chunkFileId);
                $dto->setChunkFileId(null);
                $dto->setChunkOriginalFilename(null);
            }

            $file->setConfiguration($this->uploadedFileSerializer->getConfiguration($dto));
        }

        $this->entityManager->flush();
    }
}
