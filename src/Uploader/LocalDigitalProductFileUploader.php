<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use League\Flysystem\FilesystemOperator;
use RuntimeException;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Generator\StorageFilePathGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class LocalDigitalProductFileUploader implements DigitalProductFileUploaderInterface
{
    public function __construct(
        private FilesystemOperator $localStorage,
        private StorageFilePathGeneratorInterface $storageFilePathGenerator,
        private string $uploadedFileType,
    ) {
    }

    public function upload(UploadedFile $uploadedFile): array
    {
        $ext = $uploadedFile->guessExtension() ?? pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_EXTENSION) ?: '';
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_FILENAME);
        $target = $this->storageFilePathGenerator->generate($ext);
        $stream = fopen($uploadedFile->getPathname(), 'rb');

        $this->localStorage->writeStream($target, $stream);

        if (!$this->localStorage->fileExists($target)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        return [
            self::PROPERTY_PATH => $target,
            self::PROPERTY_FILENAME => pathinfo($target, \PATHINFO_FILENAME),
            self::PROPERTY_ORIGINAL_FILENAME => $originalFilename,
            self::PROPERTY_SIZE => $this->localStorage->fileSize($target),
            self::PROPERTY_EXTENSION => $ext,
            self::PROPERTY_MIME_TYPE => $this->localStorage->mimeType($target),
        ];
    }

    public function remove(DigitalProductFileInterface $file): void
    {
        $configuration = $file->getConfiguration();
        if (empty($configuration['path']) || $this->uploadedFileType !== $file->getType()) {
            return;
        }

        $this->localStorage->delete($configuration['path']);
    }
}
