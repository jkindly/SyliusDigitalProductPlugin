<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use League\Flysystem\FilesystemOperator;
use RuntimeException;
use SyliusDigitalProductPlugin\Generator\PathGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class FilesystemChunkedUploadHandler implements ChunkedUploadHandlerInterface
{
    public function __construct(
        private FilesystemOperator $chunksStorage,
        private FilesystemOperator $localStorage,
        private PathGeneratorInterface $pathGenerator,
    ) {
    }

    public function saveChunk(string $fileId, int $chunkIndex, UploadedFile $chunk, string $originalFilename): void
    {
        $chunkPath = $this->getChunkPath($fileId, $chunkIndex, $originalFilename);
        $stream = fopen($chunk->getPathname(), 'rb');

        if (false === $stream) {
            throw new RuntimeException('Failed to open chunk file');
        }

        $this->chunksStorage->writeStream($chunkPath, $stream);
        fclose($stream);
    }

    public function mergeChunks(string $fileId, int $totalChunks, string $originalFilename): string
    {
        $mergedPath = $this->getMergedFilePathInternal($fileId, $originalFilename);
        $tempStream = tmpfile();

        if ($tempStream === false) {
            throw new RuntimeException('Failed to create temp stream');
        }

        for ($i = 0; $i < $totalChunks; ++$i) {
            $chunkPath = $this->getChunkPath($fileId, $i, $originalFilename);

            if (!$this->chunksStorage->fileExists($chunkPath)) {
                fclose($tempStream);

                throw new RuntimeException(sprintf('Chunk %d not found for file %s', $i, $fileId));
            }

            $chunkStream = $this->chunksStorage->readStream($chunkPath);

            if (!is_resource($chunkStream)) {
                fclose($tempStream);

                throw new RuntimeException(sprintf('Failed to read chunk %d', $i));
            }

            stream_copy_to_stream($chunkStream, $tempStream);
            fclose($chunkStream);

            $this->chunksStorage->delete($chunkPath);
        }

        rewind($tempStream);
        $this->chunksStorage->writeStream($mergedPath, $tempStream);
        fclose($tempStream);

        return $mergedPath;
    }

    public function moveToFinalLocation(string $fileId, string $originalFilename): string
    {
        $mergedFile = $this->getMergedFilePathInternal($fileId, $originalFilename);

        if (!$this->chunksStorage->fileExists($mergedFile)) {
            throw new RuntimeException(sprintf('Merged file not found for %s', $fileId));
        }

        $extension = pathinfo($originalFilename, \PATHINFO_EXTENSION);
        $uploadPath = sprintf('%s/%s', $this->pathGenerator->generate(), bin2hex(random_bytes(8)));
        $filename = hash('sha256', random_bytes(32) . microtime(true) . $originalFilename);
        $finalPath = sprintf('%s/%s%s', $uploadPath, $filename, $extension ? '.' . $extension : '');

        $stream = $this->chunksStorage->readStream($mergedFile);
        $this->localStorage->writeStream($finalPath, $stream);
        fclose($stream);

        $this->deleteChunks($fileId);

        return $finalPath;
    }

    public function deleteChunks(string $fileId): void
    {
        if (!$this->chunksStorage->directoryExists($fileId)) {
            return;
        }

        $this->chunksStorage->deleteDirectory($fileId);
    }

    public function hasChunks(string $fileId): bool
    {
        return $this->chunksStorage->directoryExists($fileId);
    }

    public function getMergedFilePath(string $fileId, string $originalFilename): ?string
    {
        $mergedFilePath = $this->getMergedFilePathInternal($fileId, $originalFilename);

        if (!$this->chunksStorage->fileExists($mergedFilePath)) {
            return null;
        }

        return $mergedFilePath;
    }

    private function getChunkPath(string $fileId, int $chunkIndex, string $originalFilename): string
    {
        $baseFilename = pathinfo($originalFilename, \PATHINFO_FILENAME);
        $extension = pathinfo($originalFilename, \PATHINFO_EXTENSION);

        return sprintf('%s/%s_chunk_%d%s', $fileId, $baseFilename, $chunkIndex, $extension ? '.' . $extension : '');
    }

    private function getMergedFilePathInternal(string $fileId, string $originalFilename): string
    {
        return sprintf('%s/%s', $fileId, $originalFilename);
    }
}
