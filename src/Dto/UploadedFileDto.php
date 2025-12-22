<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UploadedFileDto implements FileDtoInterface
{
    protected ?string $path = null;

    protected ?string $mimeType = null;

    protected ?string $originalFilename = null;

    protected ?string $extension = null;

    protected ?int $size = null;

    protected ?UploadedFile $uploadedFile = null;

    protected ?string $chunkFileId = null;

    protected ?string $chunkOriginalFilename = null;

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(?string $originalFilename): void
    {
        $this->originalFilename = $originalFilename;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): void
    {
        $this->extension = $extension;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function getUploadedFile(): ?UploadedFile
    {
        return $this->uploadedFile;
    }

    public function setUploadedFile(?UploadedFile $uploadedFile): void
    {
        $this->uploadedFile = $uploadedFile;
    }

    public function getChunkFileId(): ?string
    {
        return $this->chunkFileId;
    }

    public function setChunkFileId(?string $chunkFileId): void
    {
        $this->chunkFileId = $chunkFileId;
    }

    public function getChunkOriginalFilename(): ?string
    {
        return $this->chunkOriginalFilename;
    }

    public function setChunkOriginalFilename(?string $chunkOriginalFilename): void
    {
        $this->chunkOriginalFilename = $chunkOriginalFilename;
    }

    public function validateFileSource(ExecutionContextInterface $context, mixed $payload): void
    {
        $hasUploadedFile = null !== $this->uploadedFile;
        $hasChunkFile = null !== $this->chunkFileId && null !== $this->chunkOriginalFilename;
        $hasExistingFile = null !== $this->size && null !== $this->path;

        if (!$hasUploadedFile && !$hasChunkFile && !$hasExistingFile) {
            $context->buildViolation('sylius.digital_product.file.uploaded_file.not_blank')
                ->atPath('uploadedFile')
                ->addViolation()
            ;
        }
    }
}
