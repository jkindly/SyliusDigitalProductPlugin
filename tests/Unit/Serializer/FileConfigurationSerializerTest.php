<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Serializer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\ExternalUrlFileDto;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FileConfigurationSerializerTest extends TestCase
{
    private MockObject&SerializerStub $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerStub::class);
    }

    public function testGetDtoReturnsEmptyDtoForNullConfiguration(): void
    {
        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $result = $serializerService->getDto(null);

        $this->assertInstanceOf(UploadedFileDto::class, $result);
        $this->assertNull($result->getPath());
    }

    public function testGetDtoReturnsEmptyDtoForEmptyConfiguration(): void
    {
        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $result = $serializerService->getDto([]);

        $this->assertInstanceOf(UploadedFileDto::class, $result);
        $this->assertNull($result->getPath());
    }

    public function testGetDtoDenormalizesConfiguration(): void
    {
        $configuration = [
            'path' => '/path/to/file',
        ];

        $dto = new UploadedFileDto();
        $dto->setPath('/path/to/file');

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with($configuration, UploadedFileDto::class)
            ->willReturn($dto);

        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $result = $serializerService->getDto($configuration);

        $this->assertInstanceOf(UploadedFileDto::class, $result);
        $this->assertSame('/path/to/file', $result->getPath());
    }

    public function testGetDtoWithExternalUrlFileDto(): void
    {
        $configuration = [
            'url' => 'https://example.com/file.pdf',
        ];

        $dto = new ExternalUrlFileDto();
        $dto->setUrl('https://example.com/file.pdf');

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with($configuration, ExternalUrlFileDto::class)
            ->willReturn($dto);

        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            ExternalUrlFileDto::class
        );

        $result = $serializerService->getDto($configuration);

        $this->assertInstanceOf(ExternalUrlFileDto::class, $result);
        $this->assertSame('https://example.com/file.pdf', $result->getUrl());
    }

    public function testGetConfigurationReturnsEmptyArrayForNullDto(): void
    {
        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $result = $serializerService->getConfiguration(null);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetConfigurationNormalizesDto(): void
    {
        $dto = new UploadedFileDto();
        $dto->setPath('/path/to/file');

        $normalizedData = [
            'path' => '/path/to/file',
        ];

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn($normalizedData);

        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $result = $serializerService->getConfiguration($dto);

        $this->assertIsArray($result);
        $this->assertSame('/path/to/file', $result['path']);
    }

    public function testGetConfigurationThrowsExceptionWhenNormalizeReturnsNonArray(): void
    {
        $dto = new UploadedFileDto();

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn('invalid');

        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Cannot transform digital file.');

        $serializerService->getConfiguration($dto);
    }

    public function testGetConfigurationThrowsExceptionWhenNormalizeReturnsNull(): void
    {
        $dto = new UploadedFileDto();

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn(null);

        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Cannot transform digital file.');

        $serializerService->getConfiguration($dto);
    }

    public function testGetConfigurationThrowsExceptionWhenNormalizeReturnsInteger(): void
    {
        $dto = new UploadedFileDto();

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn(123);

        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Cannot transform digital file.');

        $serializerService->getConfiguration($dto);
    }

    public function testGetDtoWithComplexConfiguration(): void
    {
        $configuration = [
            'path' => '/path/to/file',
            'name' => 'test.pdf',
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
            ->method('denormalize')
            ->with($configuration, UploadedFileDto::class)
            ->willReturn($dto);

        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $result = $serializerService->getDto($configuration);

        $this->assertInstanceOf(UploadedFileDto::class, $result);
        $this->assertSame('/path/to/file', $result->getPath());
        $this->assertSame('application/pdf', $result->getMimeType());
        $this->assertSame('original.pdf', $result->getOriginalFilename());
        $this->assertSame('pdf', $result->getExtension());
        $this->assertSame(1024, $result->getSize());
    }

    public function testGetConfigurationWithComplexDto(): void
    {
        $dto = new UploadedFileDto();
        $dto->setPath('/path/to/file');
        $dto->setMimeType('application/pdf');
        $dto->setOriginalFilename('original.pdf');
        $dto->setExtension('pdf');
        $dto->setSize(1024);

        $normalizedData = [
            'path' => '/path/to/file',
            'mimeType' => 'application/pdf',
            'originalFilename' => 'original.pdf',
            'extension' => 'pdf',
            'size' => 1024,
        ];

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn($normalizedData);

        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $result = $serializerService->getConfiguration($dto);

        $this->assertIsArray($result);
        $this->assertSame('/path/to/file', $result['path']);
        $this->assertSame('application/pdf', $result['mimeType']);
        $this->assertSame('original.pdf', $result['originalFilename']);
        $this->assertSame('pdf', $result['extension']);
        $this->assertSame(1024, $result['size']);
    }

    public function testGetConfigurationReturnsEmptyArrayWhenNormalizeReturnsEmptyArray(): void
    {
        $dto = new UploadedFileDto();

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn([]);

        $serializerService = new FileConfigurationSerializer(
            $this->serializer,
            UploadedFileDto::class
        );

        $result = $serializerService->getConfiguration($dto);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}

interface SerializerStub extends DenormalizerInterface, NormalizerInterface
{
}
