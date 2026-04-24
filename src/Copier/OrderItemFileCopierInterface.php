<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Copier;

interface OrderItemFileCopierInterface
{
    public function copy(array $configuration): array;
}
