<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\DataTransformer;

use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Webmozart\Assert\Assert;

/**
 * @implements DataTransformerInterface<array, UploadedFileDto>
 */
final readonly class UploadedFileTransformer implements DataTransformerInterface
{
    public function __construct(
        private FileConfigurationSerializerInterface $uploadedFileSerializer,
    ) {
    }

    public function transform(mixed $value): UploadedFileDto
    {
        /** @var UploadedFileDto $dto */
        $dto = $this->uploadedFileSerializer->getDto($value);

        return $dto;
    }

    public function reverseTransform(mixed $value): array
    {
        Assert::isInstanceOf($value, UploadedFileDto::class);

        $configuration = $this->uploadedFileSerializer->getConfiguration($value);

        $configuration['uploadedFile'] = $value->getUploadedFile();

        return $configuration;
    }
}
