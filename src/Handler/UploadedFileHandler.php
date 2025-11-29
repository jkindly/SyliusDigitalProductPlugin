<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

use SyliusDigitalProductPlugin\Dto\FileDtoInterface;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final readonly class UploadedFileHandler implements FileHandlerInterface
{
    public function __construct(
        private DigitalProductFileUploaderInterface $localDigitalProductFileUploader,
    ) {
    }

    /**
     * @param UploadedFileDto $fileDto
     */
    public function handle(FileDtoInterface $fileDto): void
    {
        if (null === $uploadedFile = $fileDto->getUploadedFile()) {
            return;
        }

        $fileData = $this->localDigitalProductFileUploader->upload($uploadedFile);

        $fileDto->setPath($fileData[DigitalProductFileUploaderInterface::PROPERTY_PATH]);
        $fileDto->setSize($fileData[DigitalProductFileUploaderInterface::PROPERTY_SIZE]);
        $fileDto->setOriginalFilename($fileData[DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME]);
        $fileDto->setExtension($fileData[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
        $fileDto->setMimeType($fileData[DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE]);
    }
}
