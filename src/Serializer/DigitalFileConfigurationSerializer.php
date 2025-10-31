<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Serializer;

use SyliusDigitalProductPlugin\Dto\DigitalFileDtoInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final readonly class DigitalFileConfigurationSerializer implements DigitalFileConfigurationSerializerInterface
{
    public function __construct(
        private DenormalizerInterface&NormalizerInterface $serializer,
        private string $dtoClass,
    ) {
    }

    public function getDto(?array $configuration): DigitalFileDtoInterface
    {
        /** @var DigitalFileDtoInterface $dto */
        $dto = new $this->dtoClass();
        Assert::isInstanceOf($dto, DigitalFileDtoInterface::class);
        if (empty($configuration)) {
            return $dto;
        }

        /** @var DigitalFileDtoInterface $dto */
        $dto = $this->serializer->denormalize($configuration, $this->dtoClass);

        return $dto;
    }

    public function getConfiguration(?DigitalFileDtoInterface $dto): array
    {
        if (null === $dto) {
            return [];
        }

        $data = $this->serializer->normalize($dto);
        if (!is_array($data)) {
            throw new TransformationFailedException('Cannot transform digital file.');
        }

        return $data;
    }
}
