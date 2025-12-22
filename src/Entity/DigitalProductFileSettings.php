<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Resource\Model\ResourceInterface;

class DigitalProductFileSettings extends AbstractDigitalProductSettings implements
    DigitalProductFileOwnedSettingsInterface,
    ResourceInterface
{
    protected ?int $id = null;

    protected ?DigitalProductFileInterface $file = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?DigitalProductFileInterface
    {
        return $this->file;
    }

    public function setFile(?DigitalProductFileInterface $file): void
    {
        $this->file = $file;
    }
}
