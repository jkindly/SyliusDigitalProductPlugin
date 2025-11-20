<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

use SyliusDigitalProductPlugin\Dto\DigitalFileDtoInterface;
use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final readonly class UploadedDigitalFileHandler implements DigitalFileHandlerInterface
{
    public function __construct(
        private DigitalProductFileUploaderInterface $localDigitalProductFileUploader,
    ) {
    }

    /**
     * @param UploadedDigitalFileDto $digitalFileDto
     */
    public function handle(DigitalFileDtoInterface $digitalFileDto): void
    {
        if (null === $uploadedFile = $digitalFileDto->getUploadedFile()) {
            return;
        }

        $fileData = $this->localDigitalProductFileUploader->upload($uploadedFile);

        $digitalFileDto->setPath($fileData[DigitalProductFileUploaderInterface::PROPERTY_PATH]);
        $digitalFileDto->setSize($fileData[DigitalProductFileUploaderInterface::PROPERTY_SIZE]);
        $digitalFileDto->setOriginalFilename($fileData[DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME]);
        $digitalFileDto->setExtension($fileData[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
        $digitalFileDto->setMimeType($fileData[DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE]);
    }
}
