<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Handler\DigitalFileHandlerInterface;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final readonly class ProductVariantListener
{
    public function __construct(
        private DigitalFileHandlerInterface $uploadedDigitalFileHandler,
        private DigitalFileConfigurationSerializerInterface $uploadedDigitalFileSerializer,
        private EntityManagerInterface $entityManager,
        private string $uploadedDigitalFileType,
    ) {
    }

    public function handleProductVariantUpdate(GenericEvent $event): void
    {
        $productVariant = $event->getSubject();
        Assert::isInstanceOf($productVariant, DigitalProductVariantInterface::class);

        $this->handleUploadedDigitalFiles($productVariant->getDigitalFiles());
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

        $this->handleUploadedDigitalFiles($variant->getDigitalFiles());
    }

    /**
     * @param Collection<int, DigitalFileInterface> $digitalFiles
     */
    private function handleUploadedDigitalFiles(Collection $digitalFiles): void
    {
        foreach ($digitalFiles as $digitalFile) {
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
