<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Synchronizer;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Product\Model\ProductInterface;
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

    public function sync(ProductInterface $product, DigitalFileInterface $submittedFile, bool $flush = true): void
    {
        $existingFilesForProduct = $this->uploadedDigitalFileRepository->findBy(['product' => $product]);

        $existingById = [];
        foreach ($existingFilesForProduct as $existingFile) {
            $existingById[$existingFile->getId()] = $existingFile;
        }

        if (!$submittedFile instanceof UploadedDigitalFileInterface) {
            return;
        }

        if (null === $submittedFile->getProduct()) {
            $submittedFile->setProduct($product);
        }

        $handler = $this->registry->getHandlerForType($this->type);
        $handler->process($submittedFile);

        if ($submittedFile->getId() === null) {
            $this->entityManager->persist($submittedFile);
        } else {
            unset($existingById[$submittedFile->getId()]);
        }

        foreach ($existingById as $toRemove) {
            $this->entityManager->remove($toRemove);
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function supports(string $type): bool
    {
        return $this->type === $type;
    }
}
