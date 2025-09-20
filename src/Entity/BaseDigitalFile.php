<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Ramsey\Uuid\Uuid;
use Sylius\Component\Core\Model\PositionAwareInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Resource\Model\TimestampableTrait;

abstract class BaseDigitalFile implements PositionAwareInterface
{
    use TimestampableTrait;

    protected ?int $id = null;

    protected string $uuid;

    protected ?int $position = null;

    protected ?ProductInterface $product = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->getBytes();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return Uuid::fromBytes($this->uuid)->toString();
    }

    public function getUuidBytes(): string
    {
        return $this->uuid;
    }

    public function setUuidFromString(string $uuid): void
    {
        $this->uuid = Uuid::fromString($uuid)->getBytes();
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): void
    {
        $this->product = $product;
    }
}
