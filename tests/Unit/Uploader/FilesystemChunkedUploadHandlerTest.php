<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Uploader;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SyliusDigitalProductPlugin\Generator\PathGeneratorInterface;
use SyliusDigitalProductPlugin\Uploader\FilesystemChunkedUploadHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FilesystemChunkedUploadHandlerTest extends TestCase
{
    private MockObject&FilesystemOperator $chunksStorage;
    private MockObject&FilesystemOperator $localStorage;
    private MockObject&PathGeneratorInterface $pathGenerator;
    private FilesystemChunkedUploadHandler $handler;

    protected function setUp(): void
    {
        $this->chunksStorage = $this->createMock(FilesystemOperator::class);
        $this->localStorage = $this->createMock(FilesystemOperator::class);
        $this->pathGenerator = $this->createMock(PathGeneratorInterface::class);

        $this->handler = new FilesystemChunkedUploadHandler(
            $this->chunksStorage,
            $this->localStorage,
            $this->pathGenerator
        );
    }

    public function testSaveChunkWritesChunkToStorage(): void
    {
        $fileId = 'upload-123';
        $chunkIndex = 0;
        $originalFilename = 'document.pdf';

        $tempPath = tempnam(sys_get_temp_dir(), 'test_chunk_');
        file_put_contents($tempPath, 'chunk data');

        $chunk = new UploadedFile($tempPath, 'chunk', null, null, true);

        $this->chunksStorage->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->stringContains('upload-123/document_chunk_0.pdf'),
                $this->isType('resource')
            );

        $this->handler->saveChunk($fileId, $chunkIndex, $chunk, $originalFilename);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function testSaveChunkHandlesFilenameWithoutExtension(): void
    {
        $fileId = 'upload-456';
        $chunkIndex = 2;
        $originalFilename = 'README';

        $tempPath = tempnam(sys_get_temp_dir(), 'test_chunk_');
        file_put_contents($tempPath, 'readme data');

        $chunk = new UploadedFile($tempPath, 'chunk', null, null, true);

        $this->chunksStorage->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->equalTo('upload-456/README_chunk_2'),
                $this->isType('resource')
            );

        $this->handler->saveChunk($fileId, $chunkIndex, $chunk, $originalFilename);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function testSaveChunkThrowsExceptionWhenCannotOpenFile(): void
    {
        $fileId = 'upload-789';
        $chunkIndex = 0;
        $originalFilename = 'file.txt';

        $chunk = $this->createMock(UploadedFile::class);
        $chunk->method('getPathname')->willReturn('/non/existent/path');

        set_error_handler(static function (): bool {
            return true;
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to open chunk file');

        $this->handler->saveChunk($fileId, $chunkIndex, $chunk, $originalFilename);
    }

    public function testMergeChunksCreatesAndReturnsMergedFile(): void
    {
        $fileId = 'merge-123';
        $totalChunks = 3;
        $originalFilename = 'video.mp4';

        $this->chunksStorage->expects($this->exactly(3))
            ->method('fileExists')
            ->willReturnOnConsecutiveCalls(true, true, true);

        $chunk1 = fopen('php://memory', 'rb+');
        $chunk2 = fopen('php://memory', 'rb+');
        $chunk3 = fopen('php://memory', 'rb+');
        fwrite($chunk1, 'data1');
        fwrite($chunk2, 'data2');
        fwrite($chunk3, 'data3');
        rewind($chunk1);
        rewind($chunk2);
        rewind($chunk3);

        $this->chunksStorage->expects($this->exactly(3))
            ->method('readStream')
            ->willReturnOnConsecutiveCalls($chunk1, $chunk2, $chunk3);

        $this->chunksStorage->expects($this->exactly(3))
            ->method('delete');

        $this->chunksStorage->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->equalTo('merge-123/video.mp4'),
                $this->isType('resource')
            );

        $result = $this->handler->mergeChunks($fileId, $totalChunks, $originalFilename);

        $this->assertSame('merge-123/video.mp4', $result);
    }

    public function testMergeChunksThrowsExceptionWhenChunkNotFound(): void
    {
        $fileId = 'missing-chunk';
        $totalChunks = 3;
        $originalFilename = 'file.zip';

        $this->chunksStorage->expects($this->exactly(2))
            ->method('fileExists')
            ->willReturnOnConsecutiveCalls(true, false);

        $chunk1 = fopen('php://memory', 'rb+');
        fwrite($chunk1, 'data1');
        rewind($chunk1);

        $this->chunksStorage->expects($this->once())
            ->method('readStream')
            ->willReturn($chunk1);

        $this->chunksStorage->expects($this->once())
            ->method('delete');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Chunk 1 not found for file missing-chunk');

        $this->handler->mergeChunks($fileId, $totalChunks, $originalFilename);
    }

    public function testMergeChunksThrowsExceptionWhenCannotReadChunk(): void
    {
        $fileId = 'bad-chunk';
        $totalChunks = 2;
        $originalFilename = 'data.bin';

        $this->chunksStorage->expects($this->once())
            ->method('fileExists')
            ->willReturn(true);

        $this->chunksStorage->expects($this->once())
            ->method('readStream')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read chunk 0');

        $this->handler->mergeChunks($fileId, $totalChunks, $originalFilename);
    }

    public function testMoveToFinalLocationMovesFileAndReturnsPath(): void
    {
        $fileId = 'move-123';
        $originalFilename = 'document.pdf';

        $this->chunksStorage->expects($this->once())
            ->method('fileExists')
            ->with('move-123/document.pdf')
            ->willReturn(true);

        $this->pathGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('2024/01/15');

        $mergedStream = fopen('php://memory', 'rb+');
        fwrite($mergedStream, 'merged data');
        rewind($mergedStream);

        $this->chunksStorage->expects($this->once())
            ->method('readStream')
            ->with('move-123/document.pdf')
            ->willReturn($mergedStream);

        $this->localStorage->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->matchesRegularExpression('#^2024/01/15/[a-f0-9]{16}/[a-f0-9]{64}\.pdf$#'),
                $this->isType('resource')
            );

        $this->chunksStorage->expects($this->once())
            ->method('directoryExists')
            ->with('move-123')
            ->willReturn(true);

        $this->chunksStorage->expects($this->once())
            ->method('deleteDirectory')
            ->with('move-123');

        $result = $this->handler->moveToFinalLocation($fileId, $originalFilename);

        $this->assertMatchesRegularExpression('#^2024/01/15/[a-f0-9]{16}/[a-f0-9]{64}\.pdf$#', $result);
    }

    public function testMoveToFinalLocationHandlesFilenameWithoutExtension(): void
    {
        $fileId = 'move-456';
        $originalFilename = 'LICENSE';

        $this->chunksStorage->expects($this->once())
            ->method('fileExists')
            ->willReturn(true);

        $this->pathGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('2024/02/20');

        $mergedStream = fopen('php://memory', 'rb+');
        rewind($mergedStream);

        $this->chunksStorage->expects($this->once())
            ->method('readStream')
            ->willReturn($mergedStream);

        $this->localStorage->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->matchesRegularExpression('#^2024/02/20/[a-f0-9]{16}/[a-f0-9]{64}$#'),
                $this->isType('resource')
            );

        $this->chunksStorage->expects($this->once())
            ->method('directoryExists')
            ->willReturn(true);

        $this->chunksStorage->expects($this->once())
            ->method('deleteDirectory');

        $result = $this->handler->moveToFinalLocation($fileId, $originalFilename);

        $this->assertMatchesRegularExpression('#^2024/02/20/[a-f0-9]{16}/[a-f0-9]{64}$#', $result);
    }

    public function testMoveToFinalLocationThrowsExceptionWhenMergedFileNotFound(): void
    {
        $fileId = 'no-merge';
        $originalFilename = 'missing.txt';

        $this->chunksStorage->expects($this->once())
            ->method('fileExists')
            ->with('no-merge/missing.txt')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Merged file not found for no-merge');

        $this->handler->moveToFinalLocation($fileId, $originalFilename);
    }

    public function testDeleteChunksRemovesChunksDirectory(): void
    {
        $fileId = 'delete-123';

        $this->chunksStorage->expects($this->once())
            ->method('directoryExists')
            ->with($fileId)
            ->willReturn(true);

        $this->chunksStorage->expects($this->once())
            ->method('deleteDirectory')
            ->with($fileId);

        $this->handler->deleteChunks($fileId);
    }

    public function testDeleteChunksDoesNothingWhenDirectoryDoesNotExist(): void
    {
        $fileId = 'nonexistent';

        $this->chunksStorage->expects($this->once())
            ->method('directoryExists')
            ->with($fileId)
            ->willReturn(false);

        $this->chunksStorage->expects($this->never())
            ->method('deleteDirectory');

        $this->handler->deleteChunks($fileId);
    }

    public function testHasChunksReturnsTrueWhenDirectoryExists(): void
    {
        $fileId = 'has-chunks';

        $this->chunksStorage->expects($this->once())
            ->method('directoryExists')
            ->with($fileId)
            ->willReturn(true);

        $result = $this->handler->hasChunks($fileId);

        $this->assertTrue($result);
    }

    public function testHasChunksReturnsFalseWhenDirectoryDoesNotExist(): void
    {
        $fileId = 'no-chunks';

        $this->chunksStorage->expects($this->once())
            ->method('directoryExists')
            ->with($fileId)
            ->willReturn(false);

        $result = $this->handler->hasChunks($fileId);

        $this->assertFalse($result);
    }

    public function testGetMergedFilePathReturnsPathWhenFileExists(): void
    {
        $fileId = 'merged-123';
        $originalFilename = 'archive.zip';

        $this->chunksStorage->expects($this->once())
            ->method('fileExists')
            ->with('merged-123/archive.zip')
            ->willReturn(true);

        $result = $this->handler->getMergedFilePath($fileId, $originalFilename);

        $this->assertSame('merged-123/archive.zip', $result);
    }

    public function testGetMergedFilePathReturnsNullWhenFileDoesNotExist(): void
    {
        $fileId = 'no-merge';
        $originalFilename = 'file.dat';

        $this->chunksStorage->expects($this->once())
            ->method('fileExists')
            ->with('no-merge/file.dat')
            ->willReturn(false);

        $result = $this->handler->getMergedFilePath($fileId, $originalFilename);

        $this->assertNull($result);
    }

    public function testSaveChunkWithComplexFilename(): void
    {
        $fileId = 'complex-123';
        $chunkIndex = 5;
        $originalFilename = 'my-document-v2.final.pdf';

        $tempPath = tempnam(sys_get_temp_dir(), 'test_chunk_');
        file_put_contents($tempPath, 'complex data');

        $chunk = new UploadedFile($tempPath, 'chunk', null, null, true);

        $this->chunksStorage->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->equalTo('complex-123/my-document-v2.final_chunk_5.pdf'),
                $this->isType('resource')
            );

        $this->handler->saveChunk($fileId, $chunkIndex, $chunk, $originalFilename);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function testMergeChunksMergesZeroChunks(): void
    {
        $fileId = 'empty-merge';
        $totalChunks = 0;
        $originalFilename = 'empty.txt';

        $this->chunksStorage->expects($this->never())
            ->method('fileExists');

        $this->chunksStorage->expects($this->never())
            ->method('readStream');

        $this->chunksStorage->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->equalTo('empty-merge/empty.txt'),
                $this->isType('resource')
            );

        $result = $this->handler->mergeChunks($fileId, $totalChunks, $originalFilename);

        $this->assertSame('empty-merge/empty.txt', $result);
    }
}
