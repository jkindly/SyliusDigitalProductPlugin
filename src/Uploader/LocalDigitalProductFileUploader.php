<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use Random\RandomException;
use RuntimeException;
use SyliusDigitalProductPlugin\Generator\PathGeneratorInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class LocalDigitalProductFileUploader implements DigitalProductFileUploaderInterface
{
    private string $uploadPath;

    public function __construct(
        private Filesystem $filesystem,
        private PathGeneratorInterface $datePathGenerator,
        string $uploadPath,
    ) {
        $this->uploadPath = rtrim($uploadPath, '/');
    }

    public function upload(UploadedFile $uploadedFile): array
    {
        $uploadPath = sprintf('%s/%s', $this->datePathGenerator->generate(), bin2hex(random_bytes(1)));
        $absolutePath = sprintf('%s/%s', $this->uploadPath, $uploadPath,);

        $this->filesystem->mkdir($absolutePath, 0755);

        $ext = $uploadedFile->guessExtension() ?? pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_EXTENSION) ?: '';
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = hash('sha256', random_bytes(16) . microtime(true)) . ($ext ? '.' . $ext : '');
        $mimeType = $uploadedFile->getMimeType();
        $target = $absolutePath . '/' . $fileName;

        $uploadedFile->move($absolutePath, $fileName);
        if (!file_exists($target)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        $size = filesize($target);
        $relativePath = sprintf('%s/%s', trim($uploadPath, '/'), $fileName);

        return [
            self::PROPERTY_PATH => $relativePath,
            self::PROPERTY_FILENAME => $fileName,
            self::PROPERTY_ORIGINAL_FILENAME => $originalFilename,
            self::PROPERTY_SIZE => $size,
            self::PROPERTY_EXTENSION => $ext,
            self::PROPERTY_MIME_TYPE => $mimeType,
        ];
    }

    public function remove(string $storedFilename): void
    {
        // TODO: Implement remove() method.
    }
}
