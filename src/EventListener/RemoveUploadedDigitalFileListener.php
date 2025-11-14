<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener;

use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final readonly class RemoveUploadedDigitalFileListener
{
    public function __construct(
        private DigitalProductFileUploaderInterface $localDigitalProductFileUploader,
    ) {
    }

    public function preRemove(DigitalFileInterface $file): void
    {
        $this->localDigitalProductFileUploader->remove($file);
    }
}
