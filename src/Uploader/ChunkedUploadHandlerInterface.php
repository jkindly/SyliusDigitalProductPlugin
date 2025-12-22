<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ChunkedUploadHandlerInterface
{
    public function saveChunk(string $fileId, int $chunkIndex, UploadedFile $chunk, string $originalFilename): void;

    public function mergeChunks(string $fileId, int $totalChunks, string $originalFilename): string;

    public function moveToFinalLocation(string $fileId, string $originalFilename): string;

    public function deleteChunks(string $fileId): void;

    public function hasChunks(string $fileId): bool;

    public function getMergedFilePath(string $fileId, string $originalFilename): ?string;
}
