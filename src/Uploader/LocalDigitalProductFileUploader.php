<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use League\Flysystem\FilesystemOperator;
use RuntimeException;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Generator\PathGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class LocalDigitalProductFileUploader implements DigitalProductFileUploaderInterface
{
    public function __construct(
        private FilesystemOperator $localStorage,
        private PathGeneratorInterface $pathGenerator,
        private string $uploadedFileType,
    ) {
    }

    public function upload(UploadedFile $uploadedFile): array
    {
        $uploadPath = sprintf('%s/%s', $this->pathGenerator->generate(), bin2hex(random_bytes(2)));
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_FILENAME);
        $filename = hash('sha256', random_bytes(16) . microtime(true));
        $ext = $uploadedFile->guessExtension() ?? pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_EXTENSION) ?: '';
        $filenameWithExtension = $filename . ($ext ? '.' . $ext : '');
        $relativePath = sprintf('%s/%s', $uploadPath, $filenameWithExtension);
        $target = sprintf('%s/%s', $uploadPath, $filenameWithExtension);
        $stream = fopen($uploadedFile->getPathname(), 'rb');

        $this->localStorage->writeStream($target, $stream);

        if (!$this->localStorage->fileExists($target)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        return [
            self::PROPERTY_PATH => $relativePath,
            self::PROPERTY_FILENAME => $filename,
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
