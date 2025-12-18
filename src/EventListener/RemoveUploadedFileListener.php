<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener;

use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final class RemoveUploadedFileListener
{
    public function __construct(
        private DigitalProductFileUploaderInterface $localDigitalProductFileUploader,
        private bool $deleteLocalFile,
    ) {
    }

    public function preRemove(DigitalProductFileInterface $file): void
    {
        if (false === $this->deleteLocalFile) {
            return;
        }

        $this->localDigitalProductFileUploader->remove($file);
    }
}
