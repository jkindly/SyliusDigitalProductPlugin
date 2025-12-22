<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Factory;

use League\Flysystem\FilesystemOperator;
use RuntimeException;
use SyliusDigitalProductPlugin\Uploader\ChunkedUploadHandlerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class ChunkedUploadedFileFactory implements ChunkedUploadedFileFactoryInterface
{
    public function __construct(
        private FilesystemOperator $chunksStorage,
        private ChunkedUploadHandlerInterface $chunkedUploadHandler,
        private string $chunksDirectory,
    ) {
    }

    public function createFromChunk(string $fileId, string $originalFilename): UploadedFile
    {
        $mergedPath = $this->chunkedUploadHandler->getMergedFilePath($fileId, $originalFilename);

        if (null === $mergedPath || !$this->chunksStorage->fileExists($mergedPath)) {
            throw new RuntimeException(sprintf('Merged file not found for chunk ID: %s', $fileId));
        }

        $realPath = sprintf('%s/%s', rtrim($this->chunksDirectory, '/'), ltrim($mergedPath, '/'));
        if (!file_exists($realPath)) {
            throw new RuntimeException(sprintf('Physical file not found: %s', $realPath));
        }

        $mimeType = $this->chunksStorage->mimeType($mergedPath);

        return new UploadedFile(
            $realPath,
            $originalFilename,
            $mimeType,
            test: true,
        );
    }
}
