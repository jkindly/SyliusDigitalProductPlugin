<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Copier;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Copier\OrderItemFileCopier;
use SyliusDigitalProductPlugin\Generator\StorageFilePathGeneratorInterface;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final class OrderItemFileCopierTest extends TestCase
{
    private MockObject&FilesystemOperator $sourceStorage;

    private MockObject&FilesystemOperator $targetStorage;

    private MockObject&StorageFilePathGeneratorInterface $storageFilePathGenerator;

    protected function setUp(): void
    {
        $this->sourceStorage = $this->createMock(FilesystemOperator::class);
        $this->targetStorage = $this->createMock(FilesystemOperator::class);
        $this->storageFilePathGenerator = $this->createMock(StorageFilePathGeneratorInterface::class);
    }

    private function createCopier(): OrderItemFileCopier
    {
        return new OrderItemFileCopier(
            $this->sourceStorage,
            $this->targetStorage,
            $this->storageFilePathGenerator,
        );
    }

    public function testCopiesFileAndReturnsConfigurationWithUpdatedPath(): void
    {
        $sourcePath = '2024/01/01/original.pdf';
        $targetPath = '2024/01/02/copy.pdf';
        $stream = fopen('php://memory', 'r');

        $configuration = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => $sourcePath,
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'pdf',
        ];

        $this->storageFilePathGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('pdf')
            ->willReturn($targetPath);

        $this->sourceStorage
            ->expects($this->once())
            ->method('readStream')
            ->with($sourcePath)
            ->willReturn($stream);

        $this->targetStorage
            ->expects($this->once())
            ->method('writeStream')
            ->with($targetPath, $stream);

        $result = $this->createCopier()->copy($configuration);

        $this->assertSame($targetPath, $result[DigitalProductFileUploaderInterface::PROPERTY_PATH]);
        $this->assertSame('pdf', $result[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
    }

    public function testReturnsConfigurationUnchangedWhenPathIsMissing(): void
    {
        $configuration = [
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'pdf',
        ];

        $this->storageFilePathGenerator->expects($this->never())->method('generate');
        $this->sourceStorage->expects($this->never())->method('readStream');
        $this->targetStorage->expects($this->never())->method('writeStream');

        $result = $this->createCopier()->copy($configuration);

        $this->assertSame($configuration, $result);
    }

    public function testReturnsConfigurationUnchangedWhenConfigurationIsEmpty(): void
    {
        $this->storageFilePathGenerator->expects($this->never())->method('generate');
        $this->sourceStorage->expects($this->never())->method('readStream');
        $this->targetStorage->expects($this->never())->method('writeStream');

        $result = $this->createCopier()->copy([]);

        $this->assertSame([], $result);
    }

    public function testUsesEmptyStringAsExtensionWhenExtensionIsMissing(): void
    {
        $sourcePath = '2024/01/01/original';
        $targetPath = '2024/01/02/copy';
        $stream = fopen('php://memory', 'r');

        $configuration = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => $sourcePath,
        ];

        $this->storageFilePathGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('')
            ->willReturn($targetPath);

        $this->sourceStorage
            ->method('readStream')
            ->willReturn($stream);

        $this->targetStorage
            ->expects($this->once())
            ->method('writeStream')
            ->with($targetPath, $stream);

        $result = $this->createCopier()->copy($configuration);

        $this->assertSame($targetPath, $result[DigitalProductFileUploaderInterface::PROPERTY_PATH]);
    }

    public function testPreservesAllOtherConfigurationKeys(): void
    {
        $stream = fopen('php://memory', 'r');

        $configuration = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => 'old/path.zip',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'zip',
            'original_name' => 'file.zip',
            'mime_type' => 'application/zip',
        ];

        $this->storageFilePathGenerator->method('generate')->willReturn('new/path.zip');
        $this->sourceStorage->method('readStream')->willReturn($stream);

        $result = $this->createCopier()->copy($configuration);

        $this->assertSame('new/path.zip', $result[DigitalProductFileUploaderInterface::PROPERTY_PATH]);
        $this->assertSame('zip', $result[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
        $this->assertSame('file.zip', $result['original_name']);
        $this->assertSame('application/zip', $result['mime_type']);
    }

    public function testDoesNotModifySourceConfiguration(): void
    {
        $stream = fopen('php://memory', 'r');

        $configuration = [
            DigitalProductFileUploaderInterface::PROPERTY_PATH => 'old/path.pdf',
            DigitalProductFileUploaderInterface::PROPERTY_EXTENSION => 'pdf',
        ];
        $originalConfiguration = $configuration;

        $this->storageFilePathGenerator->method('generate')->willReturn('new/path.pdf');
        $this->sourceStorage->method('readStream')->willReturn($stream);

        $this->createCopier()->copy($configuration);

        $this->assertSame($originalConfiguration, $configuration);
    }
}
