<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use Psr\Log\LoggerInterface;
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
        $uploadPath = sprintf('%s/%s', $this->datePathGenerator->generate(), bin2hex(random_bytes(2)));
        $absolutePath = sprintf('%s/%s', $this->uploadPath, $uploadPath);

        $this->filesystem->mkdir($absolutePath, 0750);

        $realUploadPath = realpath($this->uploadPath);
        $realAbsolutePath = realpath($absolutePath);

        if (false === $realAbsolutePath || false === $realUploadPath || !str_starts_with($realAbsolutePath, $realUploadPath)) {
            throw new RuntimeException('Upload path validation failed');
        }

        $ext = $uploadedFile->guessExtension() ?? pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_EXTENSION) ?: '';
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_FILENAME);
        $mimeType = $uploadedFile->getMimeType();

        $filename = hash('sha256', random_bytes(32) . microtime(true));
        $filenameWithExtension = $filename . ($ext ? '.' . $ext : '');
        $target = $realAbsolutePath . '/' . $filenameWithExtension;

        if (file_exists($target)) {
            throw new RuntimeException('File with the same name already exists');
        }

        $uploadedFile->move($realAbsolutePath, $filenameWithExtension);

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

        $relativePath = $configuration['path'];
        $path = sprintf('%s/%s', $this->uploadPath, $relativePath);
        $realUploadPath = realpath($this->uploadPath);
        $realPath = realpath($path);

        if (false === $realPath) {
            throw new RuntimeException('Error resolving real path for file deletion');
        }

        if (false === $realUploadPath || !str_starts_with($realPath, $realUploadPath)) {
            throw new RuntimeException('File deletion path validation failed');
        }

        if (file_exists($realPath) && !unlink($realPath)) {
            throw new RuntimeException(sprintf('Failed to delete file: %s', $realPath));
        }
    }
}
