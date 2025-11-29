<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Form\DataTransformer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\ExternalUrlFileDto;
use SyliusDigitalProductPlugin\Form\DataTransformer\ExternalUrlFileTransformer;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerInterface;

final class ExternalUrlFileTransformerTest extends TestCase
{
    private MockObject&FileConfigurationSerializerInterface $serializer;
    private ExternalUrlFileTransformer $transformer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(FileConfigurationSerializerInterface::class);
        $this->transformer = new ExternalUrlFileTransformer($this->serializer);
    }

    public function testTransformReturnsDto(): void
    {
        $configuration = [
            'url' => 'https://example.com/file.pdf',
        ];

        $dto = new ExternalUrlFileDto();
        $dto->setUrl('https://example.com/file.pdf');

        $this->serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $result = $this->transformer->transform($configuration);

        $this->assertInstanceOf(ExternalUrlFileDto::class, $result);
        $this->assertSame('https://example.com/file.pdf', $result->getUrl());
    }

    public function testTransformWithNullValue(): void
    {
        $dto = new ExternalUrlFileDto();

        $this->serializer->expects($this->once())
            ->method('getDto')
            ->with(null)
            ->willReturn($dto);

        $result = $this->transformer->transform(null);

        $this->assertInstanceOf(ExternalUrlFileDto::class, $result);
    }

    public function testTransformWithEmptyArray(): void
    {
        $dto = new ExternalUrlFileDto();

        $this->serializer->expects($this->once())
            ->method('getDto')
            ->with([])
            ->willReturn($dto);

        $result = $this->transformer->transform([]);

        $this->assertInstanceOf(ExternalUrlFileDto::class, $result);
    }

    public function testReverseTransformReturnsConfiguration(): void
    {
        $dto = new ExternalUrlFileDto();
        $dto->setUrl('https://example.com/file.pdf');

        $configuration = [
            'url' => 'https://example.com/file.pdf',
        ];

        $this->serializer->expects($this->once())
            ->method('getConfiguration')
            ->with($dto)
            ->willReturn($configuration);

        $result = $this->transformer->reverseTransform($dto);

        $this->assertIsArray($result);
        $this->assertSame('https://example.com/file.pdf', $result['url']);
    }

    public function testReverseTransformWithEmptyDto(): void
    {
        $dto = new ExternalUrlFileDto();

        $configuration = [];

        $this->serializer->expects($this->once())
            ->method('getConfiguration')
            ->with($dto)
            ->willReturn($configuration);

        $result = $this->transformer->reverseTransform($dto);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
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

    public function testTransformWithNullUrl(): void
    {
        $configuration = [];

        $dto = new ExternalUrlFileDto();

        $this->serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $result = $this->transformer->transform($configuration);

        $this->assertInstanceOf(ExternalUrlFileDto::class, $result);
        $this->assertNull($result->getUrl());
    }

    public function testReverseTransformWithNullUrl(): void
    {
        $dto = new ExternalUrlFileDto();

        $configuration = ['url' => null];

        $this->serializer->expects($this->once())
            ->method('getConfiguration')
            ->with($dto)
            ->willReturn($configuration);

        $result = $this->transformer->reverseTransform($dto);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('url', $result);
        $this->assertNull($result['url']);
    }

    public function testTransformWithLongUrl(): void
    {
        $longUrl = 'https://example.com/very/long/path/to/file.pdf?param1=value1&param2=value2/very/long/path/to/file.pdf?param1=value1&param2=value2/very/long/path/to/file.pdf?param1=value1&param2=value2';
        $configuration = ['url' => $longUrl];

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($longUrl);

        $this->serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $result = $this->transformer->transform($configuration);

        $this->assertSame($longUrl, $result->getUrl());
    }

    public function testReverseTransformWithLongUrl(): void
    {
        $longUrl = 'https://example.com/very/long/path/to/file.pdf?param1=value1&param2=value2/very/long/path/to/file.pdf?param1=value1&param2=value2/very/long/path/to/file.pdf?param1=value1&param2=value2';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($longUrl);

        $configuration = ['url' => $longUrl];

        $this->serializer->expects($this->once())
            ->method('getConfiguration')
            ->with($dto)
            ->willReturn($configuration);

        $result = $this->transformer->reverseTransform($dto);

        $this->assertSame($longUrl, $result['url']);
    }
}
