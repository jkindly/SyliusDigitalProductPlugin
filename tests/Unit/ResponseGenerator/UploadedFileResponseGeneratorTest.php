<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\ResponseGenerator;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use SyliusDigitalProductPlugin\Provider\UploadedFileProvider;
use SyliusDigitalProductPlugin\ResponseGenerator\UploadedFileResponseGenerator;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerInterface;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerRegistry;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UploadedFileResponseGeneratorTest extends TestCase
{
    private MockObject&FilesystemOperator $localStorage;
    private FileConfigurationSerializerRegistry $serializerRegistry;
    private MockObject&FileConfigurationSerializerInterface $serializer;
    private UploadedFileResponseGenerator $generator;

    protected function setUp(): void
    {
        $this->localStorage = $this->createMock(FilesystemOperator::class);
        $this->serializer = $this->createMock(FileConfigurationSerializerInterface::class);
        $this->serializerRegistry = new FileConfigurationSerializerRegistry([
            UploadedFileProvider::TYPE => $this->serializer,
        ]);

        $this->generator = new UploadedFileResponseGenerator(
            $this->localStorage,
            $this->serializerRegistry,
            UploadedFileProvider::TYPE
        );
    }

    public function testGenerateReturnsBinaryFileResponse(): void
    {
        $filePath = '2024/01/15/test.pdf';
        $dto = $this->createDto($filePath, 'pdf');
        $file = $this->createFileWithDto('My Document', $dto);

        $this->localStorage->method('fileExists')->with($filePath)->willReturn(true);
        $this->localStorage->method('readStream')->with($filePath)->willReturn(fopen('php://memory', 'rb'));

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function testGenerateSetsContentDispositionAttachment(): void
    {
        $filePath = '2024/01/15/test.pdf';
        $dto = $this->createDto($filePath, 'pdf');
        $file = $this->createFileWithDto('My Document', $dto);

        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('readStream')->willReturn(fopen('php://memory', 'rb'));

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('My Document.pdf', $disposition);
    }

    public function testGenerateSanitizesFilenameWithExtension(): void
    {
        $filePath = '2024/01/15/test.pdf';
        $dto = $this->createDto($filePath, 'pdf');
        $file = $this->createFileWithDto('Important File', $dto);

        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('readStream')->willReturn(fopen('php://memory', 'rb'));

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('Important File.pdf', $disposition);
    }

    public function testGenerateHandlesFilenameWithoutExtension(): void
    {
        $filePath = '2024/01/15/test';
        $dto = $this->createDto($filePath, null);
        $file = $this->createFileWithDto('Document', $dto);

        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('readStream')->willReturn(fopen('php://memory', 'rb'));

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('Document', $disposition);
        $this->assertStringNotContainsString('Document.', $disposition);
    }

    public function testGenerateUsesPathWhenNameIsNull(): void
    {
        $filePath = '2024/01/15/original-file.pdf';
        $dto = $this->createDto($filePath, 'pdf');
        $file = $this->createFileWithDto(null, $dto);

        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('readStream')->willReturn(fopen('php://memory', 'rb'));

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('original-file.pdf', $disposition);
    }

    public function testGenerateUsesPathWhenNameIsEmpty(): void
    {
        $filePath = '2024/01/15/document.pdf';
        $dto = $this->createDto($filePath, 'pdf');
        $file = $this->createFileWithDto('', $dto);

        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('readStream')->willReturn(fopen('php://memory', 'rb'));

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('document.pdf', $disposition);
    }

    public function testGenerateThrowsNotFoundExceptionWhenPathIsNull(): void
    {
        $dto = $this->createDto(null, null);
        $file = $this->createFileWithDto('Test', $dto);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('File path not found in configuration.');

        $this->generator->generate($file);
    }

    public function testGenerateThrowsNotFoundExceptionWhenFileDoesNotExist(): void
    {
        $dto = $this->createDto('non/existent/file.pdf', 'pdf');
        $file = $this->createFileWithDto('Test', $dto);

        $this->localStorage->method('fileExists')->willReturn(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('File not found.');

        $this->generator->generate($file);
    }

    public function testGenerateHandlesFilePathWithLeadingSlash(): void
    {
        $filePath = '/2024/01/15/test.pdf';
        $dto = $this->createDto($filePath, 'pdf');
        $file = $this->createFileWithDto('Document', $dto);

        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('readStream')->willReturn(fopen('php://memory', 'rb'));

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function testGenerateHandlesDifferentFileTypes(): void
    {
        $filePath = '2024/01/15/archive.zip';
        $dto = $this->createDto($filePath, 'zip');
        $file = $this->createFileWithDto('My Archive', $dto);

        $this->localStorage->method('fileExists')->willReturn(true);
        $this->localStorage->method('readStream')->willReturn(fopen('php://memory', 'rb'));

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(StreamedResponse::class, $response);
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

    private function createDto(?string $path, ?string $extension = null, ?string $mimeType = null, ?int $size = null): UploadedFileDto
    {
        $dto = new UploadedFileDto();
        $dto->setPath($path);
        $dto->setExtension($extension);
        $dto->setMimeType($mimeType);
        $dto->setSize($size);

        return $dto;
    }

    private function createFileWithDto(?string $name, UploadedFileDto $dto): DigitalProductOrderItemFileInterface&MockObject
    {
        $configuration = ['path' => $dto->getPath()];

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getName')->willReturn($name);
        $file->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file->method('getConfiguration')->willReturn($configuration);

        $this->serializer->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        return $file;
    }
}
