<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener;

use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final readonly class RemoveUploadedFileListener
{
    public function __construct(
        private DigitalProductFileUploaderInterface $localDigitalProductFileUploader,
    ) {
    }

    public function preRemove(DigitalProductFileInterface $file): void
    {
        $this->localDigitalProductFileUploader->remove($file);
    }
}
