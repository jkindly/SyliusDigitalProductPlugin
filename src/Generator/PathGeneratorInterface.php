<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Generator;

interface PathGeneratorInterface
{
    public function generate(): string;
}
