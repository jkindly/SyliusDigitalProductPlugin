<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity\Trait;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemInterface;
use Webmozart\Assert\Assert;

trait DigitalProductOrderAwareTrait
{
    public function hasAnyFile(): bool
    {
        Assert::isInstanceOf($this, DigitalProductOrderInterface::class);

        foreach ($this->getItems() as $item) {
            Assert::isInstanceOf($item, DigitalProductOrderItemInterface::class);

            if ($item->getFiles()->count() > 0) {
                return true;
            }
        }

        return false;
    }

    public function getFiles(): Collection
    {
        Assert::isInstanceOf($this, DigitalProductOrderInterface::class);

        $files = new ArrayCollection();

        foreach ($this->getItems() as $item) {
            Assert::isInstanceOf($item, DigitalProductOrderItemInterface::class);

            foreach ($item->getFiles() as $file) {
                $files->add($file);
            }
        }

        return $files;
    }
}
