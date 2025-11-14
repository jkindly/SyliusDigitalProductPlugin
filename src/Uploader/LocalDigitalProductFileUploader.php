<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use RuntimeException;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Generator\PathGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class LocalDigitalProductFileUploader implements DigitalProductFileUploaderInterface
{
    private string $uploadPath;

    public function __construct(
        private Filesystem $filesystem,
        private PathGeneratorInterface $datePathGenerator,
        private bool $deleteLocalFile,
        private string $uploadedDigitalFileType,
        string $uploadPath,
    ) {
        $this->uploadPath = rtrim($uploadPath, '/');
    }

    public function upload(UploadedFile $uploadedFile): array
    {
        $uploadPath = sprintf('%s/%s', $this->datePathGenerator->generate(), bin2hex(random_bytes(1)));
        $absolutePath = sprintf('%s/%s', $this->uploadPath, $uploadPath, );

        $this->filesystem->mkdir($absolutePath, 0755);

        $ext = $uploadedFile->guessExtension() ?? pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_EXTENSION) ?: '';
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_FILENAME);
        $filename = hash('sha256', random_bytes(16) . microtime(true));
        $filenameWithExtension = $filename . ($ext ? '.' . $ext : '');
        $mimeType = $uploadedFile->getMimeType();
        $target = $absolutePath . '/' . $filenameWithExtension;

        $uploadedFile->move($absolutePath, $filenameWithExtension);
        if (!file_exists($target)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        $size = filesize($target);
        $relativePath = sprintf('%s/%s', trim($uploadPath, '/'), $filenameWithExtension);

        return [
            self::PROPERTY_PATH => $relativePath,
            self::PROPERTY_FILENAME => $filename,
            self::PROPERTY_ORIGINAL_FILENAME => $originalFilename,
            self::PROPERTY_SIZE => $size,
            self::PROPERTY_EXTENSION => $ext,
            self::PROPERTY_MIME_TYPE => $mimeType,
        ];
    }

    public function remove(DigitalFileInterface $file): void
    {
        if (false === $this->deleteLocalFile) {
            return;
        }

        $configuration = $file->getConfiguration();
        if (empty($configuration['path']) || $this->uploadedDigitalFileType !== $file->getType()) {
            return;
        }

        $path = sprintf('%s/%s', $this->uploadPath, $configuration['path']);

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
