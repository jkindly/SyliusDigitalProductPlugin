<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity\Trait;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;

trait DigitalFilesAwareTrait
{
    /** @var Collection<array-key, DigitalFileInterface> */
    protected Collection $digitalFiles;

    public function initializeFilesCollection(): void
    {
        $this->digitalFiles = new ArrayCollection();
    }

    public function getDigitalFiles(): Collection
    {
        return $this->digitalFiles;
    }

    public function addDigitalFile(DigitalFileInterface $file): void
    {
        if (!$this->digitalFiles->contains($file)) {
            $this->digitalFiles->add($file);
            $file->setProduct($this);
        }
    }

    public function removeDigitalFile(DigitalFileInterface $file): void
    {
        if ($this->digitalFiles->contains($file)) {
            $this->digitalFiles->removeElement($file);
            $file->setProduct(null);
        }
    }
}
