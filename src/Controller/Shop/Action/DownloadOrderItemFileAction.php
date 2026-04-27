<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Controller\Shop\Action;

use Doctrine\ORM\EntityManagerInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use SyliusDigitalProductPlugin\Repository\DigitalProductOrderItemFileRepositoryInterface;
use SyliusDigitalProductPlugin\ResponseGenerator\FileResponseGeneratorRegistry;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final readonly class DownloadOrderItemFileAction
{
    public function __construct(
        private DigitalProductOrderItemFileRepositoryInterface $orderItemFileRepository,
        private EntityManagerInterface $entityManager,
        private FileResponseGeneratorRegistry $responseGeneratorRegistry,
    ) {
    }

    public function __invoke(string $uuid): Response
    {
        /** @var DigitalProductOrderItemFileInterface|null $file */
        $file = $this->orderItemFileRepository->findOneByUuid($uuid);
        if (null === $file) {
            throw new NotFoundHttpException('Order item file not found.');
        }

        $downloadLimit = $file->getDownloadLimit();
        if (null !== $downloadLimit && $file->getDownloadCount() >= $downloadLimit) {
            throw new AccessDeniedException('Download limit exceeded for this file.');
        }

        if (false === $file->isAvailable()) {
            throw new AccessDeniedException('This file is no longer available for download.');
        }

        $fileType = $file->getType();
        Assert::notNull($fileType, 'File type must not be null.');

        $file->incrementDownloadCount();
        $this->entityManager->flush();

        return $this->responseGeneratorRegistry->get($fileType)->generate($file);
    }
}
