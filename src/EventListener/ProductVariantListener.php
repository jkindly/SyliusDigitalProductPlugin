<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Handler\FileHandlerInterface;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final readonly class ProductVariantListener
{
    public function __construct(
        private FileHandlerInterface $uploadedFileHandler,
        private FileConfigurationSerializerInterface $uploadedFileSerializer,
        private EntityManagerInterface $entityManager,
        private string $uploadedFileType,
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
            if (null === $uploadedFile) {
                continue;
            }

            /** @var UploadedFileDto $dto */
            $dto = $this->uploadedFileSerializer->getDto($configuration);
            $dto->setUploadedFile($uploadedFile);
            $this->uploadedFileHandler->handle($dto);

            $file->setConfiguration($this->uploadedFileSerializer->getConfiguration($dto));
        }

        $this->entityManager->flush();
    }
}
