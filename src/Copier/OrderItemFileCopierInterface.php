<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Copier;

interface OrderItemFileCopierInterface
{
    public function copy(array $configuration): array;
}
