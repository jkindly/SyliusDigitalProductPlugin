<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface DigitalProductFileUploaderInterface
{
    public const PROPERTY_PATH = 'path';

    public const PROPERTY_FILENAME = 'filename';

    public const PROPERTY_SIZE = 'size';

    public function upload(UploadedFile $uploadedFile): array;

    public function remove(string $storedFilename): void;
}
