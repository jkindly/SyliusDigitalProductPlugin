<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Generator;

interface PathGeneratorInterface
{
    public function generate(): string;
}
