<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\EventListener;

use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use Jkindly\SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final readonly class RemoveUploadedFileListener
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
