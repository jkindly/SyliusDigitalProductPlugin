<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Entity\UploadedDigitalFileInterface;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final readonly class UploadedDigitalFileHandler implements DigitalFileHandlerInterface
{
    public function __construct(
        private DigitalProductFileUploaderInterface $localDigitalProductFileUploader,
        private string $type,
    ) {
    }

    public function supports(string $type): bool
    {
        return $this->type === $type;
    }

    public function handle(DigitalFileInterface $digitalFile): void
    {
        if (!$digitalFile instanceof UploadedDigitalFileInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Wrong digital file type, expected %s, got %s',
                    UploadedDigitalFileInterface::class,
                    get_class($digitalFile),
                ),
            );
        }

        if (null === $uploadedFile = $digitalFile->getUploadedFile()) {
            throw new \InvalidArgumentException('No file was uploaded.');
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
