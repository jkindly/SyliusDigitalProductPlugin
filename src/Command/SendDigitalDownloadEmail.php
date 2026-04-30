<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Command;

final class SendDigitalDownloadEmail
{
    public function __construct(
        public readonly string $orderTokenValue,
    ) {
    }
}
