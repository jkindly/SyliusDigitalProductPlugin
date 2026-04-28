<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Generator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Generator\PathGeneratorInterface;
use SyliusDigitalProductPlugin\Generator\StorageFilePathGenerator;

final class StorageFilePathGeneratorTest extends TestCase
{
    private MockObject&PathGeneratorInterface $pathGenerator;

    protected function setUp(): void
    {
        $this->pathGenerator = $this->createMock(PathGeneratorInterface::class);
        $this->pathGenerator->method('generate')->willReturn('2024/01/01');
    }

    private function createGenerator(): StorageFilePathGenerator
    {
        return new StorageFilePathGenerator($this->pathGenerator);
    }

    public function testGenerateReturnsString(): void
    {
        $result = $this->createGenerator()->generate();

        $this->assertIsString($result);
    }

    public function testGenerateHasExpectedNumberOfSegments(): void
    {
        $result = $this->createGenerator()->generate();
        $segments = explode('/', $result);

        $this->assertCount(5, $segments);
    }

    public function testGeneratePrefixesWithPathGeneratorOutput(): void
    {
        $result = $this->createGenerator()->generate();

        $this->assertStringStartsWith('2024/01/01/', $result);
    }

    public function testGenerateWithExtensionAppendsExtensionToFilename(): void
    {
        $result = $this->createGenerator()->generate('pdf');

        $this->assertMatchesRegularExpression('/\.pdf$/', $result);
    }

    public function testGenerateWithEmptyExtensionOmitsDot(): void
    {
        $result = $this->createGenerator()->generate('');

        $segments = explode('/', $result);
        $filename = end($segments);

        $this->assertStringNotContainsString('.', $filename);
    }

    public function testGenerateWithDefaultExtensionOmitsDot(): void
    {
        $result = $this->createGenerator()->generate();

        $segments = explode('/', $result);
        $filename = end($segments);

        $this->assertStringNotContainsString('.', $filename);
    }

    public function testGenerateContainsHexSubdirectory(): void
    {
        $result = $this->createGenerator()->generate();
        $segments = explode('/', $result);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}$/', $segments[3]);
    }

    public function testGenerateContainsSha256Filename(): void
    {
        $result = $this->createGenerator()->generate('txt');
        $segments = explode('/', $result);
        $filename = pathinfo(end($segments), \PATHINFO_FILENAME);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $filename);
    }

    public function testGenerateReturnsDifferentPathsOnSubsequentCalls(): void
    {
        $generator = $this->createGenerator();

        $result1 = $generator->generate();
        $result2 = $generator->generate();

        $this->assertNotSame($result1, $result2);
    }

    public function testGenerateDelegatesPathPrefixToPathGenerator(): void
    {
        $this->pathGenerator = $this->createMock(PathGeneratorInterface::class);
        $this->pathGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('custom/prefix');

        $result = $this->createGenerator()->generate();

        $this->assertStringStartsWith('custom/prefix/', $result);
    }
}
