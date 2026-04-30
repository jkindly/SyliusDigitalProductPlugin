<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductFile;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettingsInterface;
use SyliusDigitalProductPlugin\Factory\ChunkedUploadedFileFactoryInterface;
use SyliusDigitalProductPlugin\Handler\FileHandlerInterface;
use SyliusDigitalProductPlugin\Uploader\ChunkedUploadHandlerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DigitalProductDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'sylius_digital_product_denormalizer_already_called';

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly FileHandlerInterface $uploadedFileHandler,
        private readonly ChunkedUploadedFileFactoryInterface $chunkedUploadedFileFactory,
        private readonly ChunkedUploadHandlerInterface $chunkedUploadHandler,
        private readonly string $uploadedFileType,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_array($data)) {
            return $this->denormalizer->denormalize($data, $type, $format, $context);
        }

        $files = $data['files'] ?? null;
        $variantSettings = $data['digitalProductVariantSettings'] ?? null;
        $channelSettings = $data['digitalProductFileChannelSettings'] ?? null;

        unset($data['files'], $data['digitalProductVariantSettings'], $data['digitalProductFileChannelSettings']);

        $context[self::ALREADY_CALLED] = true;

        $object = $this->denormalizer->denormalize($data, $type, $format, $context);

        if ($object instanceof DigitalProductVariantInterface) {
            $this->denormalizeVariantData($object, $files, $variantSettings, $format, $context);
        }

        if ($object instanceof DigitalProductChannelInterface) {
            $this->denormalizeChannelData($object, $channelSettings, $format, $context);
        }

        return $object;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED]) || !is_array($data)) {
            return false;
        }

        if (
            is_a($type, DigitalProductVariantInterface::class, true) &&
            (array_key_exists('files', $data) || array_key_exists('digitalProductVariantSettings', $data))
        ) {
            return true;
        }

        return
            is_a($type, ChannelInterface::class, true) &&
            array_key_exists('digitalProductFileChannelSettings', $data)
        ;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }

    private function denormalizeVariantData(
        DigitalProductVariantInterface $variant,
        mixed $files,
        mixed $variantSettings,
        ?string $format,
        array $context,
    ): void {
        if (is_array($variantSettings)) {
            $settings = $variant->getDigitalProductVariantSettings() ?? new DigitalProductVariantSettings();
            $settings = $this->denormalizer->denormalize(
                $variantSettings,
                DigitalProductVariantSettings::class,
                $format,
                [AbstractNormalizer::OBJECT_TO_POPULATE => $settings] + $context,
            );

            if ($settings instanceof DigitalProductVariantSettingsInterface) {
                $variant->setDigitalProductVariantSettings($settings);
            }
        }

        if (!is_array($files)) {
            return;
        }

        $submittedFiles = [];

        foreach ($files as $fileData) {
            if (!is_array($fileData)) {
                continue;
            }

            $file = $this->findExistingFile($variant, $fileData);
            $submittedFiles[] = $this->denormalizeFile($fileData, $file, $format, $context);
        }

        foreach ($variant->getFiles() as $existingFile) {
            if (!in_array($existingFile, $submittedFiles, true)) {
                $variant->removeFile($existingFile);
            }
        }

        foreach ($submittedFiles as $submittedFile) {
            $variant->addFile($submittedFile);
        }
    }

    private function denormalizeChannelData(
        DigitalProductChannelInterface $channel,
        mixed $channelSettings,
        ?string $format,
        array $context,
    ): void {
        if (!is_array($channelSettings)) {
            return;
        }

        $settings = $channel->getDigitalProductFileChannelSettings() ?? new DigitalProductChannelSettings();
        $settings = $this->denormalizer->denormalize(
            $channelSettings,
            DigitalProductChannelSettings::class,
            $format,
            [AbstractNormalizer::OBJECT_TO_POPULATE => $settings] + $context,
        );

        if ($settings instanceof DigitalProductChannelSettingsInterface) {
            $channel->setDigitalProductFileChannelSettings($settings);
        }
    }

    private function denormalizeFile(
        array $fileData,
        ?DigitalProductFileInterface $file,
        ?string $format,
        array $context,
    ): DigitalProductFileInterface {
        $settingsData = $fileData['settings'] ?? null;
        $channel = $this->resolveChannel($fileData['channel'] ?? null);

        unset($fileData['settings'], $fileData['channel']);

        $fileData = $this->processUploadedFileConfiguration($fileData);

        $denormalizedFile = $this->denormalizer->denormalize(
            $fileData,
            DigitalProductFile::class,
            $format,
            [AbstractNormalizer::OBJECT_TO_POPULATE => $file ?? new DigitalProductFile()] + $context,
        );

        if (!$denormalizedFile instanceof DigitalProductFileInterface) {
            return $file ?? new DigitalProductFile();
        }

        $file = $denormalizedFile;

        if (null !== $channel) {
            $file->setChannel($channel);
        }

        if (is_array($settingsData)) {
            $settings = $file->getSettings() ?? new DigitalProductFileSettings();
            $settings = $this->denormalizer->denormalize(
                $settingsData,
                DigitalProductFileSettings::class,
                $format,
                [AbstractNormalizer::OBJECT_TO_POPULATE => $settings] + $context,
            );

            if ($settings instanceof DigitalProductFileSettings) {
                $file->setSettings($settings);
            }
        }

        return $file;
    }

    private function findExistingFile(DigitalProductVariantInterface $variant, array $fileData): ?DigitalProductFileInterface
    {
        foreach ($variant->getFiles() as $file) {
            if (isset($fileData['id']) && $fileData['id'] === $file->getId()) {
                return $file;
            }

            if (isset($fileData['uuid']) && $fileData['uuid'] === $file->getUuid()) {
                return $file;
            }
        }

        return null;
    }

    private function processUploadedFileConfiguration(array $fileData): array
    {
        if (($fileData['type'] ?? null) !== $this->uploadedFileType) {
            return $fileData;
        }

        $configuration = $fileData['configuration'] ?? null;
        if (!is_array($configuration) || isset($configuration['path'])) {
            return $fileData;
        }

        $uploadedFile = null;
        $chunkFileId = $this->getStringValue($configuration, 'chunkFileId') ?? $this->getStringValue($configuration, 'fileId');
        $chunkOriginalFilename = $this->getStringValue($configuration, 'chunkOriginalFilename') ?? $this->getStringValue($configuration, 'originalFilename');
        $temporaryPath = null;

        if (null !== $chunkFileId && null !== $chunkOriginalFilename) {
            $uploadedFile = $this->chunkedUploadedFileFactory->createFromChunk($chunkFileId, $chunkOriginalFilename);
        } else {
            $originalFilename = $this->getStringValue($configuration, 'originalFilename');
            $content = $this->getStringValue($configuration, 'content') ?? $this->getStringValue($configuration, 'base64Content');

            if (null === $originalFilename || null === $content) {
                return $fileData;
            }

            $temporaryPath = $this->createTemporaryFileFromBase64Content($content);
            $uploadedFile = new UploadedFile($temporaryPath, $originalFilename, test: true);
        }

        $dto = new UploadedFileDto();
        $dto->setUploadedFile($uploadedFile);

        $this->uploadedFileHandler->handle($dto);

        if (null !== $chunkFileId) {
            $this->chunkedUploadHandler->deleteChunks($chunkFileId);
        }

        if (null !== $temporaryPath && file_exists($temporaryPath)) {
            unlink($temporaryPath);
        }

        $fileData['configuration'] = [
            'path' => $dto->getPath(),
            'mimeType' => $dto->getMimeType(),
            'originalFilename' => $dto->getOriginalFilename(),
            'extension' => $dto->getExtension(),
            'size' => $dto->getSize(),
        ];

        return $fileData;
    }

    private function createTemporaryFileFromBase64Content(string $content): string
    {
        $decodedContent = base64_decode($content, true);
        if (false === $decodedContent) {
            throw new UnexpectedValueException('Invalid uploaded file content.');
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'sylius_digital_product_api_upload_');
        if (false === $temporaryPath || false === file_put_contents($temporaryPath, $decodedContent)) {
            throw new UnexpectedValueException('Cannot create temporary uploaded file.');
        }

        return $temporaryPath;
    }

    private function getStringValue(array $data, string $key): ?string
    {
        if (!isset($data[$key]) || !is_string($data[$key]) || '' === $data[$key]) {
            return null;
        }

        return $data[$key];
    }

    private function resolveChannel(mixed $channel): ?ChannelInterface
    {
        if ($channel instanceof ChannelInterface) {
            return $channel;
        }

        if (!is_string($channel)) {
            return null;
        }

        $channel = $this->iriConverter->getResourceFromIri($channel);

        if ($channel instanceof ChannelInterface) {
            return $channel;
        }

        return null;
    }
}
