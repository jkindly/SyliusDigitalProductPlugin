<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Generator;

interface StorageFilePathGeneratorInterface
{
    public function generate(string $extension = ''): string;
}
