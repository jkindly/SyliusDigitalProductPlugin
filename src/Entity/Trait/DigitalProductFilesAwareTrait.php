<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity\Trait;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;

trait DigitalProductFilesAwareTrait
{
    /** @var Collection<array-key, DigitalProductFileInterface> */
    protected Collection $files;

    public function initializeFilesCollection(): void
    {
        $this->files = new ArrayCollection();
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function setFiles(Collection $files): void
    {
        $this->files = $files;
    }

    public function addFile(DigitalProductFileInterface $file): void
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setProductVariant($this);
        }
    }

    public function removeFile(DigitalProductFileInterface $file): void
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
            $file->setProductVariant(null);
        }
    }

    public function hasAnyFile(): bool
    {
        return !$this->files->isEmpty();
    }
}
