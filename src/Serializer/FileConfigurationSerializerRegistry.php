<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Serializer;

final readonly class FileConfigurationSerializerRegistry
{
    public function __construct(
        private iterable $serializers,
    ) {
    }

    public function get(string $fileType): FileConfigurationSerializerInterface
    {
        foreach ($this->serializers as $key => $serializer) {
            if ($key === $fileType) {
                return $serializer;
            }
        }

        throw new \RuntimeException(sprintf('No serializer found for file type "%s".', $fileType));
    }
}
