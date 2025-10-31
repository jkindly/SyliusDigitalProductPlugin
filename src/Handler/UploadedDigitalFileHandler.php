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
     * @param UploadedDigitalFileDto $digitalFile
     */
    public function handle(DigitalFileDtoInterface $digitalFile): void
    {
        if (null === $uploadedFile = $digitalFile->getUploadedFile()) {
            return;
        }

        $fileData = $this->localDigitalProductFileUploader->upload($uploadedFile);

        $digitalFile->setPath($fileData[DigitalProductFileUploaderInterface::PROPERTY_PATH]);
        $digitalFile->setSize($fileData[DigitalProductFileUploaderInterface::PROPERTY_SIZE]);
        $digitalFile->setOriginalFilename($fileData[DigitalProductFileUploaderInterface::PROPERTY_ORIGINAL_FILENAME]);
        $digitalFile->setExtension($fileData[DigitalProductFileUploaderInterface::PROPERTY_EXTENSION]);
        $digitalFile->setName($digitalFile->getName() ?? $fileData[DigitalProductFileUploaderInterface::PROPERTY_FILENAME]);
        $digitalFile->setMimeType($fileData[DigitalProductFileUploaderInterface::PROPERTY_MIME_TYPE]);
    }
}
