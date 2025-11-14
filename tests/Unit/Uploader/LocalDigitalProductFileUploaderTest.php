<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Uploader;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Generator\PathGeneratorInterface;
use SyliusDigitalProductPlugin\Provider\UploadedDigitalFileProvider;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;
use SyliusDigitalProductPlugin\Uploader\LocalDigitalProductFileUploader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class LocalDigitalProductFileUploaderTest extends TestCase
{
    private string $uploadPath;
    private Filesystem $filesystem;
    private MockObject&PathGeneratorInterface $pathGenerator;
    private LocalDigitalProductFileUploader $uploader;

    protected function setUp(): void
    {
        $this->uploadPath = sys_get_temp_dir() . '/sylius_test_uploads_' . uniqid('', true);
        $this->filesystem = new Filesystem();
        $this->pathGenerator = $this->createMock(PathGeneratorInterface::class);

        $this->filesystem->mkdir($this->uploadPath);

        $this->uploader = new LocalDigitalProductFileUploader(
            $this->filesystem,
            $this->pathGenerator,
            true,
            UploadedDigitalFileProvider::TYPE,
            $this->uploadPath
        );
    }

    protected function tearDown(): void
    {
        if ($this->filesystem->exists($this->uploadPath)) {
            $this->filesystem->remove($this->uploadPath);
        }
    }

    public function testUploadCreatesDirectoryStructure(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMockedUploadedFile('test.pdf', 'application/pdf', 'pdf');

        $this->uploader->upload($uploadedFile);

        $this->assertDirectoryExists($this->uploadPath . '/2024/01/15');
    }

    public function testUploadReturnsCorrectArrayStructure(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

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

        $uploadedFile = $this->createMockedUploadedFile('my-document.pdf', 'application/pdf', 'pdf');

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('my-document', $result[DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME]);
    }

    public function testUploadStoresExtension(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('pdf', $result[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
    }

    public function testUploadStoresMimeType(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('application/pdf', $result[DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE]);
    }

    public function testUploadStoresFileSize(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $content = str_repeat('a', 1024);
        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf', $content);

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame(1024, $result[DigitalProductFileUploaderInterface::PROPERTY_SIZE]);
    }

    public function testUploadCreatesPhysicalFile(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $result = $this->uploader->upload($uploadedFile);

        $fullPath = $this->uploadPath . '/' . $result[DigitalProductFileUploaderInterface::PROPERTY_PATH];
        $this->assertFileExists($fullPath);
    }

    public function testUploadGeneratesUniqueFilename(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

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

        $uploadedFile = $this->createMockedUploadedFile('document', 'application/octet-stream', null);

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('', $result[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
        $this->assertStringEndsNotWith('.', $result[DigitalProductFileUploaderInterface::PROPERTY_PATH]);
    }

    public function testUploadThrowsExceptionWhenFileMoveFailsAndFileDoesNotExist(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalName')->willReturn('document.pdf');
        $uploadedFile->method('guessExtension')->willReturn('pdf');
        $uploadedFile->method('getMimeType')->willReturn('application/pdf');
        $uploadedFile->method('move')->willReturn($this->createMock(File::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to move uploaded file.');

        $this->uploader->upload($uploadedFile);
    }

    public function testUploadTrimsUploadPathSlashes(): void
    {
        $uploaderWithTrailingSlash = new LocalDigitalProductFileUploader(
            $this->filesystem,
            $this->pathGenerator,
            true,
            UploadedDigitalFileProvider::TYPE,
            $this->uploadPath . '/'
        );

        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');

        $result = $uploaderWithTrailingSlash->upload($uploadedFile);

        $fullPath = $this->uploadPath . '/' . $result[DigitalProductFileUploaderInterface::PROPERTY_PATH];
        $this->assertFileExists($fullPath);
        $this->assertStringNotContainsString('//', $fullPath);
    }

    public function testUploadUsesClientOriginalExtensionWhenGuessExtensionFails(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMockedUploadedFile('document.custom', 'application/octet-stream', null);

        $result = $this->uploader->upload($uploadedFile);

        $this->assertSame('custom', $result[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
    }

    public function testRemoveDeletesFileWhenDeleteLocalFileIsTrue(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');
        $result = $this->uploader->upload($uploadedFile);

        $fullPath = $this->uploadPath . '/' . $result[DigitalProductFileUploaderInterface::PROPERTY_PATH];
        $this->assertFileExists($fullPath);

        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile->method('getConfiguration')->willReturn(['path' => $result[DigitalProductFileUploaderInterface::PROPERTY_PATH]]);

        $this->uploader->remove($digitalFile);

        $this->assertFileDoesNotExist($fullPath);
    }

    public function testRemoveDoesNotDeleteFileWhenDeleteLocalFileIsFalse(): void
    {
        $uploaderNoDelete = new LocalDigitalProductFileUploader(
            $this->filesystem,
            $this->pathGenerator,
            false,
            UploadedDigitalFileProvider::TYPE,
            $this->uploadPath
        );

        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');
        $result = $uploaderNoDelete->upload($uploadedFile);

        $fullPath = $this->uploadPath . '/' . $result[DigitalProductFileUploaderInterface::PROPERTY_PATH];
        $this->assertFileExists($fullPath);

        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile->method('getConfiguration')->willReturn(['path' => $result[DigitalProductFileUploaderInterface::PROPERTY_PATH]]);

        $uploaderNoDelete->remove($digitalFile);

        $this->assertFileExists($fullPath);
    }

    public function testRemoveDoesNotDeleteFileWhenTypeDoesNotMatch(): void
    {
        $this->pathGenerator->method('generate')->willReturn('2024/01/15');

        $uploadedFile = $this->createMockedUploadedFile('document.pdf', 'application/pdf', 'pdf');
        $result = $this->uploader->upload($uploadedFile);

        $fullPath = $this->uploadPath . '/' . $result[DigitalProductFileUploaderInterface::PROPERTY_PATH];
        $this->assertFileExists($fullPath);

        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn('external_url');
        $digitalFile->method('getConfiguration')->willReturn(['path' => $result[DigitalProductFileUploaderInterface::PROPERTY_PATH]]);

        $this->uploader->remove($digitalFile);

        $this->assertFileExists($fullPath);
    }

    public function testRemoveDoesNotDeleteFileWhenPathIsEmpty(): void
    {
        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile->method('getConfiguration')->willReturn(['path' => '']);

        $this->uploader->remove($digitalFile);

        $this->assertTrue(true);
    }

    public function testRemoveDoesNotThrowExceptionWhenFileDoesNotExist(): void
    {
        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile->method('getConfiguration')->willReturn(['path' => 'non/existent/file.pdf']);

        $this->uploader->remove($digitalFile);

        $this->assertTrue(true);
    }

    private function createMockedUploadedFile(
        string $filename,
        string $mimeType,
        ?string $guessedExtension,
        string $content = 'test content'
    ): UploadedFile {
        $tempFile = sys_get_temp_dir() . '/' . uniqid() . '.tmp';
        file_put_contents($tempFile, $content);

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$tempFile, $filename, $mimeType, null, true])
            ->onlyMethods(['guessExtension', 'getMimeType', 'move'])
            ->getMock();

        $uploadedFile->method('guessExtension')->willReturn($guessedExtension);
        $uploadedFile->method('getMimeType')->willReturn($mimeType);
        $uploadedFile->method('move')->willReturnCallback(function ($directory, $name) use ($tempFile) {
            $targetPath = $directory . '/' . $name;
            copy($tempFile, $targetPath);
            return new File($targetPath);
        });

        return $uploadedFile;
    }
}
