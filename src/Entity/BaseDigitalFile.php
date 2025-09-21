<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Resource\Model\TimestampableTrait;
use Symfony\Component\Uid\Uuid;

abstract class BaseDigitalFile implements DigitalFileInterface
{
    use TimestampableTrait;

    protected ?int $id = null;

    protected string $uuid;

    protected ?int $position = null;

    protected ?ProductInterface $product = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4()->toRfc4122();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
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
