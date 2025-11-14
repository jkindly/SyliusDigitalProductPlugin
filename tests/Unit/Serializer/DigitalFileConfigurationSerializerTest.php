<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Serializer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\ExternalUrlDigitalFileDto;
use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DigitalFileConfigurationSerializerTest extends TestCase
{
    private MockObject&SerializerStub $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerStub::class);
    }

    public function testGetDtoReturnsEmptyDtoForNullConfiguration(): void
    {
        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $result = $serializerService->getDto(null);

        $this->assertInstanceOf(UploadedDigitalFileDto::class, $result);
        $this->assertNull($result->getPath());
        $this->assertNull($result->getName());
    }

    public function testGetDtoReturnsEmptyDtoForEmptyConfiguration(): void
    {
        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $result = $serializerService->getDto([]);

        $this->assertInstanceOf(UploadedDigitalFileDto::class, $result);
        $this->assertNull($result->getPath());
        $this->assertNull($result->getName());
    }

    public function testGetDtoDenormalizesConfiguration(): void
    {
        $configuration = [
            'path' => '/path/to/file',
            'name' => 'test.pdf',
        ];

        $dto = new UploadedDigitalFileDto();
        $dto->setPath('/path/to/file');
        $dto->setName('test.pdf');

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with($configuration, UploadedDigitalFileDto::class)
            ->willReturn($dto);

        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $result = $serializerService->getDto($configuration);

        $this->assertInstanceOf(UploadedDigitalFileDto::class, $result);
        $this->assertSame('/path/to/file', $result->getPath());
        $this->assertSame('test.pdf', $result->getName());
    }

    public function testGetDtoWithExternalUrlDigitalFileDto(): void
    {
        $configuration = [
            'url' => 'https://example.com/file.pdf',
        ];

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl('https://example.com/file.pdf');

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with($configuration, ExternalUrlDigitalFileDto::class)
            ->willReturn($dto);

        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            ExternalUrlDigitalFileDto::class
        );

        $result = $serializerService->getDto($configuration);

        $this->assertInstanceOf(ExternalUrlDigitalFileDto::class, $result);
        $this->assertSame('https://example.com/file.pdf', $result->getUrl());
    }

    public function testGetConfigurationReturnsEmptyArrayForNullDto(): void
    {
        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $result = $serializerService->getConfiguration(null);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetConfigurationNormalizesDto(): void
    {
        $dto = new UploadedDigitalFileDto();
        $dto->setPath('/path/to/file');
        $dto->setName('test.pdf');

        $normalizedData = [
            'path' => '/path/to/file',
            'name' => 'test.pdf',
        ];

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn($normalizedData);

        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $result = $serializerService->getConfiguration($dto);

        $this->assertIsArray($result);
        $this->assertSame('/path/to/file', $result['path']);
        $this->assertSame('test.pdf', $result['name']);
    }

    public function testGetConfigurationThrowsExceptionWhenNormalizeReturnsNonArray(): void
    {
        $dto = new UploadedDigitalFileDto();

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn('invalid');

        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Cannot transform digital file.');

        $serializerService->getConfiguration($dto);
    }

    public function testGetConfigurationThrowsExceptionWhenNormalizeReturnsNull(): void
    {
        $dto = new UploadedDigitalFileDto();

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn(null);

        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Cannot transform digital file.');

        $serializerService->getConfiguration($dto);
    }

    public function testGetConfigurationThrowsExceptionWhenNormalizeReturnsInteger(): void
    {
        $dto = new UploadedDigitalFileDto();

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn(123);

        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
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

        $dto = new UploadedDigitalFileDto();
        $dto->setPath('/path/to/file');
        $dto->setName('test.pdf');
        $dto->setMimeType('application/pdf');
        $dto->setOriginalFilename('original.pdf');
        $dto->setExtension('pdf');
        $dto->setSize(1024);

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with($configuration, UploadedDigitalFileDto::class)
            ->willReturn($dto);

        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $result = $serializerService->getDto($configuration);

        $this->assertInstanceOf(UploadedDigitalFileDto::class, $result);
        $this->assertSame('/path/to/file', $result->getPath());
        $this->assertSame('test.pdf', $result->getName());
        $this->assertSame('application/pdf', $result->getMimeType());
        $this->assertSame('original.pdf', $result->getOriginalFilename());
        $this->assertSame('pdf', $result->getExtension());
        $this->assertSame(1024, $result->getSize());
    }

    public function testGetConfigurationWithComplexDto(): void
    {
        $dto = new UploadedDigitalFileDto();
        $dto->setPath('/path/to/file');
        $dto->setName('test.pdf');
        $dto->setMimeType('application/pdf');
        $dto->setOriginalFilename('original.pdf');
        $dto->setExtension('pdf');
        $dto->setSize(1024);

        $normalizedData = [
            'path' => '/path/to/file',
            'name' => 'test.pdf',
            'mimeType' => 'application/pdf',
            'originalFilename' => 'original.pdf',
            'extension' => 'pdf',
            'size' => 1024,
        ];

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn($normalizedData);

        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $result = $serializerService->getConfiguration($dto);

        $this->assertIsArray($result);
        $this->assertSame('/path/to/file', $result['path']);
        $this->assertSame('test.pdf', $result['name']);
        $this->assertSame('application/pdf', $result['mimeType']);
        $this->assertSame('original.pdf', $result['originalFilename']);
        $this->assertSame('pdf', $result['extension']);
        $this->assertSame(1024, $result['size']);
    }

    public function testGetConfigurationReturnsEmptyArrayWhenNormalizeReturnsEmptyArray(): void
    {
        $dto = new UploadedDigitalFileDto();

        $this->serializer->expects($this->once())
            ->method('normalize')
            ->with($dto)
            ->willReturn([]);

        $serializerService = new DigitalFileConfigurationSerializer(
            $this->serializer,
            UploadedDigitalFileDto::class
        );

        $result = $serializerService->getConfiguration($dto);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}

interface SerializerStub extends DenormalizerInterface, NormalizerInterface
{
}
