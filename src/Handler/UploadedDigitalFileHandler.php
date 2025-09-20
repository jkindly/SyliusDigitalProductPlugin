<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;

final readonly class UploadedDigitalFileHandler implements DigitalFileHandlerInterface
{
    public function __construct(private string $type)
    {
    }

    public function supports(string $type): bool
    {
        return $this->type === $type;
    }

    public function process(DigitalFileInterface $digitalFile): void
    {
        // TODO: Implement process() method.
    }
}
