<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Entity\UploadedDigitalFileInterface;

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
        if (!$digitalFile instanceof UploadedDigitalFileInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Wrong digital file type, expected %s, got %s',
                    UploadedDigitalFileInterface::class,
                    get_class($digitalFile),
                ),
            );
        }

    }
}
