<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

use SyliusDigitalProductPlugin\Dto\FileDtoInterface;

interface FileHandlerInterface
{
    public function handle(FileDtoInterface $fileDto): void;
}
