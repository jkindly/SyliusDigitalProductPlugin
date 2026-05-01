<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Copier;

use Jkindly\SyliusDigitalProductPlugin\Generator\StorageFilePathGeneratorInterface;
use Jkindly\SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;
use League\Flysystem\FilesystemOperator;

final readonly class OrderItemFileCopier implements OrderItemFileCopierInterface
{
    public function __construct(
        private FilesystemOperator $sourceStorage,
        private FilesystemOperator $targetStorage,
        private StorageFilePathGeneratorInterface $storageFilePathGenerator,
    ) {
    }

    public function copy(array $configuration): array
    {
        $sourcePath = $configuration[DigitalProductFileUploaderInterface::PROPERTY_PATH] ?? null;
        if (null === $sourcePath) {
            return $configuration;
        }

        $extension = $configuration[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION] ?? '';
        $targetPath = $this->storageFilePathGenerator->generate($extension);

        $stream = $this->sourceStorage->readStream($sourcePath);
        $this->targetStorage->writeStream($targetPath, $stream);

        $configuration[DigitalProductFileUploaderInterface::PROPERTY_PATH] = $targetPath;

        return $configuration;
    }
}
