<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

use SyliusDigitalProductPlugin\Dto\DigitalFileDtoInterface;

interface DigitalFileHandlerInterface
{
    public function handle(DigitalFileDtoInterface $digitalFile): void;
}
