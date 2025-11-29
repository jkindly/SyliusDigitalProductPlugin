<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\DataTransformer;

use SyliusDigitalProductPlugin\Dto\ExternalUrlFileDto;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Webmozart\Assert\Assert;

/**
 * @implements DataTransformerInterface<array, ExternalUrlFileDto>
 */
final readonly class ExternalUrlFileTransformer implements DataTransformerInterface
{
    public function __construct(
        private FileConfigurationSerializerInterface $externalUrlFileSerializer,
    ) {
    }

    public function transform(mixed $value): ExternalUrlFileDto
    {
        /** @var ExternalUrlFileDto $dto */
        $dto = $this->externalUrlFileSerializer->getDto($value);

        return $dto;
    }

    public function reverseTransform(mixed $value): array
    {
        Assert::isInstanceOf($value, ExternalUrlFileDto::class);

        return $this->externalUrlFileSerializer->getConfiguration($value);
    }
}
