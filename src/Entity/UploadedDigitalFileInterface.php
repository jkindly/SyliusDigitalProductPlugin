<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface UploadedDigitalFileInterface extends ResourceInterface
{
    public function getPath(): ?string;

    public function setPath(?string $path): void;

    public function getMimeType(): ?string;

    public function setMimeType(?string $mimeType): void;

    public function getOriginalFilename(): ?string;

    public function setOriginalFilename(?string $originalFilename): void;

    public function getSize(): ?int;

    public function setSize(?int $size): void;
}
