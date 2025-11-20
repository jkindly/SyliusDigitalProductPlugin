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
    public function hasAnyDigitalFile(): bool
    {
        Assert::isInstanceOf($this, DigitalProductOrderInterface::class);

        foreach ($this->getItems() as $item) {
            Assert::isInstanceOf($item, DigitalProductOrderItemInterface::class);

            if ($item->getDigitalFiles()->count() > 0) {
                return true;
            }
        }

        return false;
    }

    public function getDigitalFiles(): Collection
    {
        Assert::isInstanceOf($this, DigitalProductOrderInterface::class);

        $digitalFiles = new ArrayCollection();

        foreach ($this->getItems() as $item) {
            Assert::isInstanceOf($item, DigitalProductOrderItemInterface::class);

            foreach ($item->getDigitalFiles() as $digitalFile) {
                $digitalFiles->add($digitalFile);
            }
        }

        return $digitalFiles;
    }
}
