<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Synchronizer;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Entity\UploadedDigitalFileInterface;
use SyliusDigitalProductPlugin\Handler\DigitalFileHandlerRegistry;
use SyliusDigitalProductPlugin\Repository\UploadedDigitalFileRepositoryInterface;

final readonly class UploadedDigitalFileSynchronizer implements DigitalFileSynchronizerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DigitalFileHandlerRegistry $registry,
        private UploadedDigitalFileRepositoryInterface $uploadedDigitalFileRepository,
        private string $type,
    ) {
    }

    public function sync(ProductInterface $product, array $submittedFiles, bool $flush = true): void
    {
        $existingFilesForProduct = $this->uploadedDigitalFileRepository->findBy(['product' => $product]);

        $existingById = [];
        foreach ($existingFilesForProduct as $existingFile) {
            $existingById[$existingFile->getId()] = $existingFile;
        }

        foreach ($submittedFiles as $file) {
            if (!$file instanceof UploadedDigitalFileInterface) {
                continue;
            }

            if (null === $file->getProduct()) {
                $file->setProduct($product);
            }

            $handler = $this->registry->getHandlerForType($this->type);
            $handler->process($file);

            if ($file->getId() === null) {
                $this->entityManager->persist($file);
            } else {
                unset($existingById[$file->getId()]);
            }
        }

        foreach ($existingById as $toRemove) {
            $this->entityManager->remove($toRemove);
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
