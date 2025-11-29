<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\ResponseGenerator;

use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use SyliusDigitalProductPlugin\Provider\UploadedFileProvider;
use SyliusDigitalProductPlugin\ResponseGenerator\UploadedFileResponseGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UploadedFileResponseGeneratorTest extends TestCase
{
    private string $uploadPath;
    private Filesystem $filesystem;
    private UploadedFileResponseGenerator $generator;

    protected function setUp(): void
    {
        $this->uploadPath = sys_get_temp_dir() . '/response_generator_test_' . uniqid('', true);
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->uploadPath);

        $this->generator = new UploadedFileResponseGenerator(
            UploadedFileProvider::TYPE,
            $this->uploadPath
        );
    }

    protected function tearDown(): void
    {
        if ($this->filesystem->exists($this->uploadPath)) {
            $this->filesystem->remove($this->uploadPath);
        }
    }

    public function testGenerateReturnsBinaryFileResponse(): void
    {
        $filePath = '2024/01/15/test.pdf';
        $this->createTestFile($filePath, 'test content');

        $dto = new UploadedFileDto();
        $dto->setPath($filePath);
        $dto->setExtension('pdf');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn('My Document');

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    public function testGenerateSetsContentDispositionAttachment(): void
    {
        $filePath = '2024/01/15/test.pdf';
        $this->createTestFile($filePath, 'test content');

        $dto = new UploadedFileDto();
        $dto->setPath($filePath);
        $dto->setExtension('pdf');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn('My Document');

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('My Document.pdf', $disposition);
    }

    public function testGenerateSanitizesFilenameWithExtension(): void
    {
        $filePath = '2024/01/15/test.pdf';
        $this->createTestFile($filePath, 'test content');

        $dto = new UploadedFileDto();
        $dto->setPath($filePath);
        $dto->setExtension('pdf');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn('Important File');

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('Important File.pdf', $disposition);
    }

    public function testGenerateHandlesFilenameWithoutExtension(): void
    {
        $filePath = '2024/01/15/test';
        $this->createTestFile($filePath, 'test content');

        $dto = new UploadedFileDto();
        $dto->setPath($filePath);
        $dto->setExtension(null);

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn('Document');

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('Document', $disposition);
        $this->assertStringNotContainsString('Document.', $disposition);
    }

    public function testGenerateUsesPathWhenNameIsNull(): void
    {
        $filePath = '2024/01/15/original-file.pdf';
        $this->createTestFile($filePath, 'test content');

        $dto = new UploadedFileDto();
        $dto->setPath($filePath);
        $dto->setExtension('pdf');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn(null);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('original-file.pdf', $disposition);
    }

    public function testGenerateUsesPathWhenNameIsEmpty(): void
    {
        $filePath = '2024/01/15/document.pdf';
        $this->createTestFile($filePath, 'test content');

        $dto = new UploadedFileDto();
        $dto->setPath($filePath);
        $dto->setExtension('pdf');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn('');

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('document.pdf', $disposition);
    }

    public function testGenerateThrowsNotFoundExceptionWhenPathIsNull(): void
    {
        $dto = new UploadedFileDto();
        $dto->setPath(null);

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('File path not found in configuration.');

        $this->generator->generate($file, $dto);
    }

    public function testGenerateThrowsNotFoundExceptionWhenFileDoesNotExist(): void
    {
        $dto = new UploadedFileDto();
        $dto->setPath('non/existent/file.pdf');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn('Test');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('File not found or path validation failed.');

        $this->generator->generate($file, $dto);
    }

    public function testGenerateValidatesPathTraversal(): void
    {
        $this->createTestFile('legitimate.pdf', 'test content');

        $dto = new UploadedFileDto();
        $dto->setPath('../../etc/passwd');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('File not found or path validation failed.');

        $this->generator->generate($file, $dto);
    }

    public function testGeneratePreventDirectoryTraversalAttack(): void
    {
        $dto = new UploadedFileDto();
        $dto->setPath('../../../sensitive-file.txt');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $this->expectException(NotFoundHttpException::class);

        $this->generator->generate($file, $dto);
    }

    public function testGenerateHandlesFilePathWithLeadingSlash(): void
    {
        $filePath = '2024/01/15/test.pdf';
        $this->createTestFile($filePath, 'test content');

        $dto = new UploadedFileDto();
        $dto->setPath('/' . $filePath);
        $dto->setExtension('pdf');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn('Document');

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    public function testGenerateHandlesDifferentFileTypes(): void
    {
        $filePath = '2024/01/15/archive.zip';
        $this->createTestFile($filePath, 'zip content');

        $dto = new UploadedFileDto();
        $dto->setPath($filePath);
        $dto->setExtension('zip');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn('My Archive');

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('My Archive.zip', $disposition);
    }

    public function testSupportsReturnsTrueForUploadedFileType(): void
    {
        $this->assertTrue($this->generator->supports(UploadedFileProvider::TYPE));
    }

    public function testSupportsReturnsFalseForOtherFileTypes(): void
    {
        $this->assertFalse($this->generator->supports('external_url'));
        $this->assertFalse($this->generator->supports('s3_file'));
        $this->assertFalse($this->generator->supports('random_type'));
    }

    private function createTestFile(string $relativePath, string $content): void
    {
        $fullPath = $this->uploadPath . '/' . $relativePath;
        $directory = dirname($fullPath);

        $this->filesystem->mkdir($directory);
        file_put_contents($fullPath, $content);
    }
}
