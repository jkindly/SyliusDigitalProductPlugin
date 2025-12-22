<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Factory;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ChunkedUploadedFileFactoryInterface
{
    public function createFromChunk(string $fileId, string $originalFilename): UploadedFile;
}
