<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\DataTransformer;

use SyliusDigitalProductPlugin\Dto\ExternalUrlDigitalFileDto;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Webmozart\Assert\Assert;

/**
 * @implements DataTransformerInterface<array, ExternalUrlDigitalFileDto>
 */
final readonly class ExternalUrlDigitalFileTransformer implements DataTransformerInterface
{
    public function __construct(
        private DigitalFileConfigurationSerializerInterface $externalUrlDigitalFileSerializer,
    ) {
    }

    public function transform(mixed $value): ExternalUrlDigitalFileDto
    {
        /** @var ExternalUrlDigitalFileDto $dto */
        $dto = $this->externalUrlDigitalFileSerializer->getDto($value);

        return $dto;
    }

    public function reverseTransform(mixed $value): array
    {
        Assert::isInstanceOf($value, ExternalUrlDigitalFileDto::class);

        return $this->externalUrlDigitalFileSerializer->getConfiguration($value);
    }
}
