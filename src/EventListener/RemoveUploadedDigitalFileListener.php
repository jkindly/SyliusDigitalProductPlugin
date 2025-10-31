<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener;

use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Provider\UploadedDigitalFileProvider;

final readonly class RemoveUploadedDigitalFileListener
{
    public function __construct(
        private bool $deleteLocalFile,
        private string $uploadPath,
    ) {
    }

    public function preRemove(DigitalFileInterface $file): void
    {
        if (false === $this->deleteLocalFile) {
            return;
        }

        $configuration = $file->getConfiguration();
        if (empty($configuration['path']) || UploadedDigitalFileProvider::TYPE !== $file->getType()) {
            return;
        }

        $path = sprintf('%s/%s', rtrim($this->uploadPath, '/'), $configuration['path']);

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
