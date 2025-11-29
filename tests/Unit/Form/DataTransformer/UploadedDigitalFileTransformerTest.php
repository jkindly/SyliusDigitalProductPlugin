<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Form\DataTransformer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Form\DataTransformer\UploadedFileTransformer;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadedFileTransformerTest extends TestCase
{
    private MockObject&FileConfigurationSerializerInterface $serializer;
    private UploadedFileTransformer $transformer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(FileConfigurationSerializerInterface::class);
        $this->transformer = new UploadedFileTransformer($this->serializer);
    }

    public function testTransformReturnsDto(): void
    {
        $configuration = [
            'path' => '/path/to/file',
        ];

        $dto = new UploadedFileDto();
        $dto->setPath('/path/to/file');

        $this->serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $result = $this->transformer->transform($configuration);

        $this->assertInstanceOf(UploadedFileDto::class, $result);
        $this->assertSame('/path/to/file', $result->getPath());
    }

    public function testTransformWithNullValue(): void
    {
        $dto = new UploadedFileDto();

        $this->serializer->expects($this->once())
            ->method('getDto')
            ->with(null)
            ->willReturn($dto);

        $result = $this->transformer->transform(null);

        $this->assertInstanceOf(UploadedFileDto::class, $result);
    }

    public function testTransformWithEmptyArray(): void
    {
        $dto = new UploadedFileDto();

        $this->serializer->expects($this->once())
            ->method('getDto')
            ->with([])
            ->willReturn($dto);

        $result = $this->transformer->transform([]);

        $this->assertInstanceOf(UploadedFileDto::class, $result);
    }

    public function testReverseTransformReturnsConfiguration(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);

        $dto = new UploadedFileDto();
        $dto->setPath('/path/to/file');
        $dto->setUploadedFile($uploadedFile);

        $configuration = [
            'path' => '/path/to/file',
        ];

        $this->serializer->expects($this->once())
            ->method('getConfiguration')
            ->with($dto)
            ->willReturn($configuration);

        $result = $this->transformer->reverseTransform($dto);

        $this->assertIsArray($result);
        $this->assertSame('/path/to/file', $result['path']);
        $this->assertSame($uploadedFile, $result['uploadedFile']);
    }

    public function testReverseTransformAddsUploadedFileToConfiguration(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);

        $dto = new UploadedFileDto();
        $dto->setUploadedFile($uploadedFile);

        $configuration = ['name' => 'test.pdf'];

        $this->serializer->expects($this->once())
            ->method('getConfiguration')
            ->with($dto)
            ->willReturn($configuration);

        $result = $this->transformer->reverseTransform($dto);

        $this->assertArrayHasKey('uploadedFile', $result);
        $this->assertSame($uploadedFile, $result['uploadedFile']);
    }

    public function testReverseTransformWithNullUploadedFile(): void
    {
        $dto = new UploadedFileDto();
        $dto->setPath('/path/to/file');

        $configuration = ['path' => '/path/to/file'];

        $this->serializer->expects($this->once())
            ->method('getConfiguration')
            ->with($dto)
            ->willReturn($configuration);

        $result = $this->transformer->reverseTransform($dto);

        $this->assertArrayHasKey('uploadedFile', $result);
        $this->assertNull($result['uploadedFile']);
    }

    public function testReverseTransformThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->transformer->reverseTransform(['invalid' => 'data']);
    }

    public function testReverseTransformThrowsExceptionForNullValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->transformer->reverseTransform(null);
    }

    public function testReverseTransformThrowsExceptionForStringValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->transformer->reverseTransform('invalid');
    }

    public function testTransformPreservesAllDtoProperties(): void
    {
        $configuration = [
            'path' => '/path/to/file',
            'mimeType' => 'application/pdf',
            'originalFilename' => 'original.pdf',
            'extension' => 'pdf',
            'size' => 1024,
        ];

        $dto = new UploadedFileDto();
        $dto->setPath('/path/to/file');
        $dto->setMimeType('application/pdf');
        $dto->setOriginalFilename('original.pdf');
        $dto->setExtension('pdf');
        $dto->setSize(1024);

        $this->serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $result = $this->transformer->transform($configuration);

        $this->assertSame('/path/to/file', $result->getPath());
        $this->assertSame('application/pdf', $result->getMimeType());
        $this->assertSame('original.pdf', $result->getOriginalFilename());
        $this->assertSame('pdf', $result->getExtension());
        $this->assertSame(1024, $result->getSize());
    }

    public function testReverseTransformPreservesAllConfigurationData(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);

        $dto = new UploadedFileDto();
        $dto->setPath('/path/to/file');
        $dto->setMimeType('application/pdf');
        $dto->setOriginalFilename('original.pdf');
        $dto->setExtension('pdf');
        $dto->setSize(1024);
        $dto->setUploadedFile($uploadedFile);

        $configuration = [
            'path' => '/path/to/file',
            'mimeType' => 'application/pdf',
            'originalFilename' => 'original.pdf',
            'extension' => 'pdf',
            'size' => 1024,
        ];

        $this->serializer->expects($this->once())
            ->method('getConfiguration')
            ->with($dto)
            ->willReturn($configuration);

        $result = $this->transformer->reverseTransform($dto);

        $this->assertSame('/path/to/file', $result['path']);
        $this->assertSame('application/pdf', $result['mimeType']);
        $this->assertSame('original.pdf', $result['originalFilename']);
        $this->assertSame('pdf', $result['extension']);
        $this->assertSame(1024, $result['size']);
        $this->assertSame($uploadedFile, $result['uploadedFile']);
    }
}
