<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Serializer;

use SyliusDigitalProductPlugin\Dto\DigitalFileDtoInterface;

interface DigitalFileConfigurationSerializerInterface
{
    public function getDto(?array $configuration): DigitalFileDtoInterface;

    public function getConfiguration(?DigitalFileDtoInterface $dto): array;
}
