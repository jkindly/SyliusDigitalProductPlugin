<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Handler\UploadedFileHandler;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadedFileHandlerTest extends TestCase
{
    private MockObject&DigitalProductFileUploaderInterface $uploader;

    private UploadedFileHandler $handler;

    protected function setUp(): void
    {
        $this->uploader = $this->createMock(DigitalProductFileUploaderInterface::class);
        $this->handler = new UploadedFileHandler($this->uploader);
    }

    public function testHandleDoesNothingWhenUploadedFileIsNull(): void
    {
        $file = $this->createMock(UploadedFileDto::class);
        $file->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn(null);

        $this->uploader->expects($this->never())
            ->method('upload');

        $file->expects($this->never())
            ->method('setPath');

        $this->handler->handle($file);
    }

    public function testHandleUploadsFileAndSetsAllProperties(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $file = $this->createMock(UploadedFileDto::class);

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => 1024000,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'document.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'pdf',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'abc123_document.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'application/pdf',
        ];

        $file->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $file->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.pdf');

        $file->expects($this->once())
            ->method('setSize')
            ->with(1024000);

        $file->expects($this->once())
            ->method('setOriginalFilename')
            ->with('document.pdf');

        $file->expects($this->once())
            ->method('setExtension')
            ->with('pdf');

        $file->expects($this->once())
            ->method('setMimeType')
            ->with('application/pdf');

        $this->handler->handle($file);
    }

    public function testHandleUsesFilenameFromUploadDataWhenNameIsNull(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $file = $this->createMock(UploadedFileDto::class);

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => 500,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'original.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'pdf',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'generated_filename.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'application/pdf',
        ];

        $file->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $file->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.pdf');

        $file->expects($this->once())
            ->method('setSize')
            ->with(500);

        $file->expects($this->once())
            ->method('setOriginalFilename')
            ->with('original.pdf');

        $file->expects($this->once())
            ->method('setExtension')
            ->with('pdf');

        $file->expects($this->once())
            ->method('setMimeType')
            ->with('application/pdf');

        $this->handler->handle($file);
    }

    public function testHandlePreservesEmptyStringName(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $file = $this->createMock(UploadedFileDto::class);

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => 500,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'original.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'pdf',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'fallback_filename.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'application/pdf',
        ];

        $file->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $file->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.pdf');

        $file->expects($this->once())
            ->method('setSize')
            ->with(500);

        $file->expects($this->once())
            ->method('setOriginalFilename')
            ->with('original.pdf');

        $file->expects($this->once())
            ->method('setExtension')
            ->with('pdf');

        $file->expects($this->once())
            ->method('setMimeType')
            ->with('application/pdf');

        $this->handler->handle($file);
    }

    public function testHandleWorksWithDifferentFileTypes(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $file = $this->createMock(UploadedFileDto::class);

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => 2048000,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'photo.jpg',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'jpg',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'photo_12345.jpg',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'image/jpeg',
        ];

        $file->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $file->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.pdf');

        $file->expects($this->once())
            ->method('setSize')
            ->with(2048000);

        $file->expects($this->once())
            ->method('setOriginalFilename')
            ->with('photo.jpg');

        $file->expects($this->once())
            ->method('setExtension')
            ->with('jpg');

        $file->expects($this->once())
            ->method('setMimeType')
            ->with('image/jpeg');

        $this->handler->handle($file);
    }

    public function testHandleWorksWithLargeFiles(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $file = $this->createMock(UploadedFileDto::class);

        $largeFileSize = 5368709120;

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.zip',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => $largeFileSize,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'large-file.zip',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'zip',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'large_file_hash.zip',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'application/zip',
        ];

        $file->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $file->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.zip');

        $file->expects($this->once())
            ->method('setSize')
            ->with($largeFileSize);

        $file->expects($this->once())
            ->method('setOriginalFilename')
            ->with('large-file.zip');

        $file->expects($this->once())
            ->method('setExtension')
            ->with('zip');

        $file->expects($this->once())
            ->method('setMimeType')
            ->with('application/zip');

        $this->handler->handle($file);
    }
}
