<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Command;

final class SendDigitalDownloadEmail
{
    public function __construct(
        public readonly string $orderTokenValue,
    ) {
    }
}
