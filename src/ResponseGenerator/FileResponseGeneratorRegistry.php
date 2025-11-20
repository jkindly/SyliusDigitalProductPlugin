<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\ResponseGenerator;

final readonly class FileResponseGeneratorRegistry
{
    public function __construct(
        private iterable $generators,
    ) {
    }

    public function get(string $fileType): FileResponseGeneratorInterface
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($fileType)) {
                return $generator;
            }
        }

        throw new \RuntimeException(sprintf('No response generator found for file type "%s".', $fileType));
    }
}
