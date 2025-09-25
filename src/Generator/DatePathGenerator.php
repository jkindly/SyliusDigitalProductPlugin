<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Generator;

final class DatePathGenerator implements PathGeneratorInterface
{
    public function generate(): string
    {
        $date = (new \DateTimeImmutable())->format('Y/m/d');

        return implode('/', [$date]);
    }
}
