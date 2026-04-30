<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductFile;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettings;
use SyliusDigitalProductPlugin\Factory\ChunkedUploadedFileFactoryInterface;
use SyliusDigitalProductPlugin\Handler\FileHandlerInterface;
use SyliusDigitalProductPlugin\Provider\UploadedFileProvider;
use SyliusDigitalProductPlugin\Serializer\DigitalProductDenormalizer;
use SyliusDigitalProductPlugin\Uploader\ChunkedUploadHandlerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Tests\SyliusDigitalProductPlugin\Entity\Channel;
use Tests\SyliusDigitalProductPlugin\Entity\ProductVariant;

final class DigitalProductDenormalizerTest extends TestCase
{
    private DigitalProductDenormalizer $denormalizer;

    private Channel $channel;

    private MockObject&FileHandlerInterface $uploadedFileHandler;

    private MockObject&ChunkedUploadedFileFactoryInterface $chunkedUploadedFileFactory;

    private MockObject&ChunkedUploadHandlerInterface $chunkedUploadHandler;

    protected function setUp(): void
    {
        $this->channel = new Channel();
        $this->uploadedFileHandler = $this->createMock(FileHandlerInterface::class);
        $this->chunkedUploadedFileFactory = $this->createMock(ChunkedUploadedFileFactoryInterface::class);
        $this->chunkedUploadHandler = $this->createMock(ChunkedUploadHandlerInterface::class);

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter
            ->method('getResourceFromIri')
            ->with('/api/v2/admin/channels/FASHION_WEB')
            ->willReturn($this->channel)
        ;

        $innerDenormalizer = $this->createMock(DenormalizerInterface::class);
        $innerDenormalizer->method('denormalize')->willReturnCallback($this->denormalizeCallback());

        $this->denormalizer = new DigitalProductDenormalizer(
            $iriConverter,
            $this->uploadedFileHandler,
            $this->chunkedUploadedFileFactory,
            $this->chunkedUploadHandler,
            UploadedFileProvider::TYPE,
        );
        $this->denormalizer->setDenormalizer($innerDenormalizer);
    }

    public function testItDenormalizesVariantDigitalFields(): void
    {
        $variant = $this->denormalizer->denormalize([
            'files' => [[
                'name' => 'Manual',
                'type' => 'external_url',
                'channel' => '/api/v2/admin/channels/FASHION_WEB',
                'configuration' => ['url' => 'https://example.com/manual.pdf'],
                'settings' => [
                    'downloadLimit' => 3,
                    'daysAvailable' => 14,
                ],
            ]],
            'digitalProductVariantSettings' => [
                'enabled' => true,
                'hiddenQuantity' => true,
            ],
        ], ProductVariant::class);

        self::assertInstanceOf(ProductVariant::class, $variant);
        self::assertCount(1, $variant->getFiles());
        self::assertInstanceOf(DigitalProductVariantSettings::class, $variant->getDigitalProductVariantSettings());
        self::assertTrue($variant->getDigitalProductVariantSettings()->isEnabled());
        self::assertTrue($variant->getDigitalProductVariantSettings()->isHiddenQuantity());

        $file = $variant->getFiles()->first();

        self::assertInstanceOf(DigitalProductFile::class, $file);
        self::assertSame($variant, $file->getProductVariant());
        self::assertSame($this->channel, $file->getChannel());
        self::assertSame('Manual', $file->getName());
        self::assertSame('external_url', $file->getType());
        self::assertSame(['url' => 'https://example.com/manual.pdf'], $file->getConfiguration());
        self::assertInstanceOf(DigitalProductFileSettings::class, $file->getSettings());
        self::assertSame(3, $file->getSettings()->getDownloadLimit());
        self::assertSame(14, $file->getSettings()->getDaysAvailable());
    }

    public function testItReplacesVariantFilesWhenFilesAreSubmitted(): void
    {
        $variant = new ProductVariant();
        $keptFile = new DigitalProductFile();
        $removedFile = new DigitalProductFile();

        $variant->addFile($keptFile);
        $variant->addFile($removedFile);

        $result = $this->denormalizer->denormalize([
            'files' => [[
                'uuid' => $keptFile->getUuid(),
                'name' => 'Updated manual',
                'type' => 'external_url',
            ]],
        ], ProductVariant::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $variant,
        ]);

        self::assertSame($variant, $result);
        self::assertCount(1, $variant->getFiles());
        self::assertTrue($variant->getFiles()->contains($keptFile));
        self::assertFalse($variant->getFiles()->contains($removedFile));
        self::assertSame('Updated manual', $keptFile->getName());
        self::assertNull($removedFile->getProductVariant());
    }

    public function testItDenormalizesChannelDigitalSettings(): void
    {
        $channel = $this->denormalizer->denormalize([
            'digitalProductFileChannelSettings' => [
                'downloadLimit' => 5,
                'daysAvailable' => 30,
                'hiddenQuantity' => true,
            ],
        ], Channel::class);

        self::assertInstanceOf(Channel::class, $channel);
        self::assertInstanceOf(DigitalProductChannelSettings::class, $channel->getDigitalProductFileChannelSettings());
        self::assertSame(5, $channel->getDigitalProductFileChannelSettings()->getDownloadLimit());
        self::assertSame(30, $channel->getDigitalProductFileChannelSettings()->getDaysAvailable());
        self::assertTrue($channel->getDigitalProductFileChannelSettings()->isHiddenQuantity());
        self::assertSame($channel, $channel->getDigitalProductFileChannelSettings()->getChannel());
    }

    public function testItProcessesBase64UploadedFileConfiguration(): void
    {
        $this->uploadedFileHandler
            ->expects(self::once())
            ->method('handle')
            ->with(self::callback(function (UploadedFileDto $dto): bool {
                self::assertInstanceOf(UploadedFile::class, $dto->getUploadedFile());
                self::assertSame('manual.txt', $dto->getUploadedFile()->getClientOriginalName());
                self::assertSame('API file content', file_get_contents($dto->getUploadedFile()->getPathname()));

                $dto->setPath('2026/04/30/manual.txt');
                $dto->setMimeType('text/plain');
                $dto->setOriginalFilename('manual');
                $dto->setExtension('txt');
                $dto->setSize(16);

                return true;
            }))
        ;

        $variant = $this->denormalizer->denormalize([
            'files' => [[
                'name' => 'Manual',
                'type' => UploadedFileProvider::TYPE,
                'configuration' => [
                    'originalFilename' => 'manual.txt',
                    'content' => base64_encode('API file content'),
                ],
            ]],
        ], ProductVariant::class);

        self::assertInstanceOf(ProductVariant::class, $variant);

        $file = $variant->getFiles()->first();

        self::assertInstanceOf(DigitalProductFile::class, $file);
        self::assertSame([
            'path' => '2026/04/30/manual.txt',
            'mimeType' => 'text/plain',
            'originalFilename' => 'manual',
            'extension' => 'txt',
            'size' => 16,
        ], $file->getConfiguration());
    }

    public function testItProcessesChunkedUploadedFileConfiguration(): void
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'sylius_digital_product_test_chunk_');
        self::assertIsString($temporaryPath);
        file_put_contents($temporaryPath, 'chunk content');

        $uploadedFile = new UploadedFile($temporaryPath, 'manual.pdf', 'application/pdf', test: true);

        $this->chunkedUploadedFileFactory
            ->expects(self::once())
            ->method('createFromChunk')
            ->with('chunk-id', 'manual.pdf')
            ->willReturn($uploadedFile)
        ;

        $this->uploadedFileHandler
            ->expects(self::once())
            ->method('handle')
            ->with(self::callback(function (UploadedFileDto $dto): bool {
                self::assertSame('manual.pdf', $dto->getUploadedFile()?->getClientOriginalName());

                $dto->setPath('2026/04/30/manual.pdf');
                $dto->setMimeType('application/pdf');
                $dto->setOriginalFilename('manual');
                $dto->setExtension('pdf');
                $dto->setSize(12);

                return true;
            }))
        ;

        $this->chunkedUploadHandler
            ->expects(self::once())
            ->method('deleteChunks')
            ->with('chunk-id')
        ;

        $variant = $this->denormalizer->denormalize([
            'files' => [[
                'name' => 'Manual',
                'type' => UploadedFileProvider::TYPE,
                'configuration' => [
                    'fileId' => 'chunk-id',
                    'originalFilename' => 'manual.pdf',
                ],
            ]],
        ], ProductVariant::class);

        self::assertInstanceOf(ProductVariant::class, $variant);

        $file = $variant->getFiles()->first();

        self::assertInstanceOf(DigitalProductFile::class, $file);
        self::assertSame([
            'path' => '2026/04/30/manual.pdf',
            'mimeType' => 'application/pdf',
            'originalFilename' => 'manual',
            'extension' => 'pdf',
            'size' => 12,
        ], $file->getConfiguration());

        if (file_exists($temporaryPath)) {
            unlink($temporaryPath);
        }
    }

    private function denormalizeCallback(): callable
    {
        return function (mixed $data, string $type, ?string $format = null, array $context = []): object {
            $object = $context[AbstractNormalizer::OBJECT_TO_POPULATE] ?? new $type();

            if (!is_array($data)) {
                return $object;
            }

            if ($object instanceof DigitalProductFile) {
                if (!isset($data['name']) || is_string($data['name'])) {
                    $object->setName($data['name'] ?? $object->getName());
                }

                if (!isset($data['type']) || is_string($data['type'])) {
                    $object->setType($data['type'] ?? $object->getType());
                }

                if (isset($data['configuration']) && is_array($data['configuration'])) {
                    $object->setConfiguration($data['configuration']);
                }
            }

            if ($object instanceof DigitalProductFileSettings || $object instanceof DigitalProductChannelSettings) {
                if (!isset($data['downloadLimit']) || is_int($data['downloadLimit'])) {
                    $object->setDownloadLimit($data['downloadLimit'] ?? $object->getDownloadLimit());
                }

                if (!isset($data['daysAvailable']) || is_int($data['daysAvailable'])) {
                    $object->setDaysAvailable($data['daysAvailable'] ?? $object->getDaysAvailable());
                }
            }

            if ($object instanceof DigitalProductVariantSettings) {
                if (!isset($data['enabled']) || is_bool($data['enabled'])) {
                    $object->setEnabled($data['enabled'] ?? $object->isEnabled());
                }

                if (!isset($data['hiddenQuantity']) || is_bool($data['hiddenQuantity'])) {
                    $object->setHiddenQuantity($data['hiddenQuantity'] ?? $object->isHiddenQuantity());
                }
            }

            if ($object instanceof DigitalProductChannelSettings) {
                if (!isset($data['hiddenQuantity']) || is_bool($data['hiddenQuantity'])) {
                    $object->setHiddenQuantity($data['hiddenQuantity'] ?? $object->isHiddenQuantity());
                }
            }

            return $object;
        };
    }
}
