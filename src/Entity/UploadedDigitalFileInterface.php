<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadedDigitalFileInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getPath(): ?string;

    public function setPath(?string $path): void;

    public function getMimeType(): ?string;

    public function setMimeType(?string $mimeType): void;

    public function getOriginalFilename(): ?string;

    public function setOriginalFilename(?string $originalFilename): void;

    public function getExtension(): ?string;

    public function setExtension(?string $extension): void;

    public function getSize(): ?int;

    public function setSize(?int $size): void;

    public function getUploadedFile(): ?UploadedFile;

    public function setUploadedFile(?UploadedFile $uploadedFile): void;
}
