<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Uploader;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Generator\PathGeneratorInterface;
use SyliusDigitalProductPlugin\Provider\UploadedFileProvider;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;
use SyliusDigitalProductPlugin\Uploader\LocalDigitalProductFileUploader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class LocalDigitalProductFileUploaderTest extends TestCase
{
    private MockObject&FilesystemOperator $localStorage;
    private MockObject&PathGeneratorInterface $pathGenerator;
    private LocalDigitalProductFileUploader $uploader;

    protected function setUp(): void
    {
        $this->localStorage = $this->createMock(FilesystemOperator::class);
        $this->pathGenerator = $this->createMock(PathGeneratorInterface::class);

        $this->uploader = new LocalDigitalProductFileUploader(
            $this->localStorage,
            $this->pathGenerator,
            UploadedFileProvider::TYPE
        );
    }

    public function testUploadReturnsCorrectArrayStructure(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('fileSize')->willReturn(1024);
        $this->localStorage->method('mimeType')->willReturn('application/pdf');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $result = $this->uploader->upload($uploadedFile);

        $this->assertArrayHasKey(DigitalProductFileUploaderInterface::PROPERTY_PATH, $result);
        $this->assertArrayHasKey(DigitalProductFileUploaderInterface::PROPERTY_FILENAME, $result);
        $this->assertArrayHasKey(DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME, $result);
        $this->assertArrayHasKey(DigitalProductFileUploaderInterface::PROPERTY_SIZE, $result);
        $this->assertArrayHasKey(DigitalProductFileUploaderInterface::PROPERTY_EXTENSION, $result);
        $this->assertArrayHasKey(DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE, $result);
    }

    public function testUploadStoresOriginalFilename(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('fileSize')->willReturn(1024);
        $this->localStorage->method('mimeType')->willReturn('application/pdf');

        $uploadedFile = $this->createMockedUploadedFile('my-document.pdf', 'application/pdf', 'pdf');

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('my-document', $result[DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME]);
    }

    public function testUploadStoresExtension(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('fileSize')->willReturn(1024);
        $this->localStorage->method('mimeType')->willReturn('application/pdf');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('pdf', $result[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
    }

    public function testUploadStoresMimeType(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('fileSize')->willReturn(1024);
        $this->localStorage->method('mimeType')->willReturn('application/pdf');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('application/pdf', $result[DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE]);
    }

    public function testUploadStoresFileSize(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('fileSize')->willReturn(1024);
        $this->localStorage->method('mimeType')->willReturn('application/pdf');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame(1024, $result[DigitalProductFileUploaderInterface::PROPERTY_SIZE]);
    }

    public function testUploadCallsFlysystemWriteStream(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->expects($this->once())->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('fileSize')->willReturn(1024);
        $this->localStorage->method('mimeType')->willReturn('application/pdf');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $this->uploader->upload($uploadedFile);
    }

    public function testUploadGeneratesUniqueFilename(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('fileSize')->willReturn(1024);
        $this->localStorage->method('mimeType')->willReturn('application/pdf');

        $uploadedFile1 = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');
        $uploadedFile2 = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $result1 = $this->uploader->upload($uploadedFile1);
        $result2 = $this->uploader->upload($uploadedFile2);

        $this->assertNotSame(
            $result1[DigitalProductFileUploaderInterface::PROPERTY_FILENAME],
            $result2[DigitalProductFileUploaderInterface::PROPERTY_FILENAME]
        );
    }

    public function testUploadHandlesFileWithoutExtension(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('fileSize')->willReturn(1024);
        $this->localStorage->method('mimeType')->willReturn('application/octet-stream');

        $uploadedFile = $this->createMockedUploadedFile('document', 'application/octet-stream', null);

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('', $result[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
        $this->assertStringEndsNotWith('.', $result[DigitalProductFileUploaderInterface::PROPERTY_PATH]);
    }

    public function testUploadThrowsExceptionWhenFileDoesNotExist(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(false);

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to move uploaded file.');

        $this->uploader->upload($uploadedFile);
    }

    public function testUploadUsesClientOriginalExtensionWhenGuessExtensionFails(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');
        $this->localStorage->method('writeStream');
        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('fileSize')->willReturn(1024);
        $this->localStorage->method('mimeType')->willReturn('application/octet-stream');

        $uploadedFile = $this->createMockedUploadedFile('document.custom', 'application/octet-stream', null);

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('custom', $result[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
    }

    public function testRemoveDeletesFile(): void
    {
        $this->localStorage->expects($this->once())
            ->method('delete')
            ->with('2024/01/15/test.pdf');

        $file = $this->createMock(DigitalProductFileInterface::class);
        $file->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file->method('getConfiguration')->willReturn(['path' => '2024/01/15/test.pdf']);

        $this->uploader->remove($file);
    }

    public function testRemoveDoesNotDeleteFileWhenTypeDoesNotMatch(): void
    {
        $this->localStorage->expects($this->never())->method('delete');

        $file = $this->createMock(DigitalProductFileInterface::class);
        $file->method('getType')->willReturn('external_url');
        $file->method('getConfiguration')->willReturn(['path' => '2024/01/15/test.pdf']);

        $this->uploader->remove($file);
    }

    public function testRemoveDoesNotDeleteFileWhenPathIsEmpty(): void
    {
        $this->localStorage->expects($this->never())->method('delete');

        $file = $this->createMock(DigitalProductFileInterface::class);
        $file->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file->method('getConfiguration')->willReturn(['path' => '']);

        $this->uploader->remove($file);
    }

    private function createMockedUploadedFile(
        string $filename,
        string $mimeType,
        ?string $guessedExtension
    ): UploadedFile {
        $tempFile = sys_get_temp_dir() . '/' . uniqid() . '.tmp';
        file_put_contents($tempFile, 'test content');

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$tempFile, $filename, $mimeType, null, true])
            ->onlyMethods(['guessExtension'])
            ->getMock();

        $uploadedFile->method('guessExtension')->willReturn($guessedExtension);

        return $uploadedFile;
    }
}
