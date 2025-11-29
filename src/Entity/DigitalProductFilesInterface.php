<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;

interface DigitalProductFilesInterface
{
    /**
     * @return Collection<int, DigitalProductFileInterface>
     */
    public function getFiles(): Collection;

    /**
     * @param Collection<int, DigitalProductFileInterface> $files
     */
    public function setFiles(Collection $files): void;

    public function addFile(DigitalProductFileInterface $file): void;

    public function removeFile(DigitalProductFileInterface $file): void;
}
