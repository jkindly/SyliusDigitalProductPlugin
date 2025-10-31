<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\DataTransformer;

use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Webmozart\Assert\Assert;

/**
 * @implements DataTransformerInterface<array, UploadedDigitalFileDto>
 */
final readonly class UploadedDigitalFileTransformer implements DataTransformerInterface
{
    public function __construct(
        private DigitalFileConfigurationSerializerInterface $uploadedDigitalFileSerializer,
    ) {
    }

    public function transform(mixed $value): UploadedDigitalFileDto
    {
        /** @var UploadedDigitalFileDto $dto */
        $dto = $this->uploadedDigitalFileSerializer->getDto($value);

        return $dto;
    }

    public function reverseTransform(mixed $value): array
    {
        Assert::isInstanceOf($value, UploadedDigitalFileDto::class);

        $configuration = $this->uploadedDigitalFileSerializer->getConfiguration($value);

        $configuration['uploadedFile'] = $value->getUploadedFile();

        return $configuration;
    }
}
