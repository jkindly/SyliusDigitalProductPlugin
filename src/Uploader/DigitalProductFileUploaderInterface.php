<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface DigitalProductFileUploaderInterface
{
    public function upload(UploadedFile $uploadedFile): string;

    public function remove(string $storedFilename): void;
}
