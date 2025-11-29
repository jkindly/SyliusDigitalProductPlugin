<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Serializer;

use SyliusDigitalProductPlugin\Dto\FileDtoInterface;

interface FileConfigurationSerializerInterface
{
    public function getDto(?array $configuration): FileDtoInterface;

    public function getConfiguration(?FileDtoInterface $dto): array;
}
