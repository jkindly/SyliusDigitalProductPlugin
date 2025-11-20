<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Handler\UploadedDigitalFileHandler;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadedDigitalFileHandlerTest extends TestCase
{
    private MockObject&DigitalProductFileUploaderInterface $uploader;

    private UploadedDigitalFileHandler $handler;

    protected function setUp(): void
    {
        $this->uploader = $this->createMock(DigitalProductFileUploaderInterface::class);
        $this->handler = new UploadedDigitalFileHandler($this->uploader);
    }

    public function testHandleDoesNothingWhenUploadedFileIsNull(): void
    {
        $digitalFile = $this->createMock(UploadedDigitalFileDto::class);
        $digitalFile->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn(null);

        $this->uploader->expects($this->never())
            ->method('upload');

        $digitalFile->expects($this->never())
            ->method('setPath');

        $this->handler->handle($digitalFile);
    }

    public function testHandleUploadsFileAndSetsAllProperties(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $digitalFile = $this->createMock(UploadedDigitalFileDto::class);

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => 1024000,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'document.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'pdf',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'abc123_document.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'application/pdf',
        ];

        $digitalFile->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $digitalFile->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.pdf');

        $digitalFile->expects($this->once())
            ->method('setSize')
            ->with(1024000);

        $digitalFile->expects($this->once())
            ->method('setOriginalFilename')
            ->with('document.pdf');

        $digitalFile->expects($this->once())
            ->method('setExtension')
            ->with('pdf');

        $digitalFile->expects($this->once())
            ->method('setMimeType')
            ->with('application/pdf');

        $this->handler->handle($digitalFile);
    }

    public function testHandleUsesFilenameFromUploadDataWhenNameIsNull(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $digitalFile = $this->createMock(UploadedDigitalFileDto::class);

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => 500,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'original.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'pdf',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'generated_filename.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'application/pdf',
        ];

        $digitalFile->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $digitalFile->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.pdf');

        $digitalFile->expects($this->once())
            ->method('setSize')
            ->with(500);

        $digitalFile->expects($this->once())
            ->method('setOriginalFilename')
            ->with('original.pdf');

        $digitalFile->expects($this->once())
            ->method('setExtension')
            ->with('pdf');

        $digitalFile->expects($this->once())
            ->method('setMimeType')
            ->with('application/pdf');

        $this->handler->handle($digitalFile);
    }

    public function testHandlePreservesEmptyStringName(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $digitalFile = $this->createMock(UploadedDigitalFileDto::class);

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => 500,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'original.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'pdf',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'fallback_filename.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'application/pdf',
        ];

        $digitalFile->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $digitalFile->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.pdf');

        $digitalFile->expects($this->once())
            ->method('setSize')
            ->with(500);

        $digitalFile->expects($this->once())
            ->method('setOriginalFilename')
            ->with('original.pdf');

        $digitalFile->expects($this->once())
            ->method('setExtension')
            ->with('pdf');

        $digitalFile->expects($this->once())
            ->method('setMimeType')
            ->with('application/pdf');

        $this->handler->handle($digitalFile);
    }

    public function testHandleWorksWithDifferentFileTypes(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $digitalFile = $this->createMock(UploadedDigitalFileDto::class);

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => 2048000,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'photo.jpg',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'jpg',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'photo_12345.jpg',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'image/jpeg',
        ];

        $digitalFile->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $digitalFile->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.pdf');

        $digitalFile->expects($this->once())
            ->method('setSize')
            ->with(2048000);

        $digitalFile->expects($this->once())
            ->method('setOriginalFilename')
            ->with('photo.jpg');

        $digitalFile->expects($this->once())
            ->method('setExtension')
            ->with('jpg');

        $digitalFile->expects($this->once())
            ->method('setMimeType')
            ->with('image/jpeg');

        $this->handler->handle($digitalFile);
    }

    public function testHandleWorksWithLargeFiles(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $digitalFile = $this->createMock(UploadedDigitalFileDto::class);

        $largeFileSize = 5368709120;

        $uploadData = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => '2024/11/13/file.zip',
            DigitalProductFileUploaderInterface::PROPERTY_SIZE => $largeFileSize,
            DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME => 'large-file.zip',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'zip',
            DigitalProductFileUploaderInterface::PROPERTY_FILENAME => 'large_file_hash.zip',
            DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE => 'application/zip',
        ];

        $digitalFile->expects($this->once())
            ->method('getUploadedFile')
            ->willReturn($uploadedFile);

        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($uploadData);

        $digitalFile->expects($this->once())
            ->method('setPath')
            ->with('2024/11/13/file.zip');

        $digitalFile->expects($this->once())
            ->method('setSize')
            ->with($largeFileSize);

        $digitalFile->expects($this->once())
            ->method('setOriginalFilename')
            ->with('large-file.zip');

        $digitalFile->expects($this->once())
            ->method('setExtension')
            ->with('zip');

        $digitalFile->expects($this->once())
            ->method('setMimeType')
            ->with('application/zip');

        $this->handler->handle($digitalFile);
    }
}
