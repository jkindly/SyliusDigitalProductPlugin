<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Generator;

final readonly class StorageFilePathGenerator implements StorageFilePathGeneratorInterface
{
    public function __construct(private PathGeneratorInterface $pathGenerator)
    {
    }

    public function generate(string $extension = ''): string
    {
        $filename = hash('sha256', random_bytes(16) . microtime(true));
        $filenameWithExtension = $filename . ('' !== $extension ? '.' . $extension : '');

        return sprintf('%s/%s/%s', $this->pathGenerator->generate(), bin2hex(random_bytes(4)), $filenameWithExtension);
    }
}
