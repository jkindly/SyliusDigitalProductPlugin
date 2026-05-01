<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Command;

final readonly class SendDigitalDownloadEmail
{
    public function __construct(
        public string $orderTokenValue,
    ) {
    }
}
