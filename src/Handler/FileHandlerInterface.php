<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Handler;

use Jkindly\SyliusDigitalProductPlugin\Dto\FileDtoInterface;

interface FileHandlerInterface
{
    public function handle(FileDtoInterface $fileDto): void;
}
