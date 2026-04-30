<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Serializer;

use Jkindly\SyliusDigitalProductPlugin\Dto\FileDtoInterface;

interface FileConfigurationSerializerInterface
{
    public function getDto(?array $configuration): FileDtoInterface;

    public function getConfiguration(?FileDtoInterface $dto): array;
}
