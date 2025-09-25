<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class LocalDigitalProductFileUploader implements DigitalProductFileUploaderInterface
{
    private string $uploadPath;

    public function __construct(
        private Filesystem $filesystem,
        string $uploadPath,
    ) {
        $this->uploadPath = rtrim($uploadPath, '/');
    }

    public function upload(UploadedFile $uploadedFile): string
    {
        $dir = $this->uploadPath . '/' . bin2hex(random_bytes(1));
        $this->filesystem->mkdir($dir, 0755);
    }

    public function remove(string $storedFilename): void
    {
        // TODO: Implement remove() method.
    }
}
