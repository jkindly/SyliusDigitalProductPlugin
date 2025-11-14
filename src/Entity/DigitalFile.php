<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Resource\Model\TimestampableTrait;
use Symfony\Component\Uid\Uuid;

class DigitalFile implements DigitalFileInterface
{
    use TimestampableTrait;

    protected ?int $id = null;

    protected string $uuid;

    protected ?string $type = null;

    protected ?int $position = null;

    protected ?ProductVariantInterface $productVariant = null;

    protected array $configuration = [];

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getProductVariant(): ?ProductVariantInterface
    {
        return $this->productVariant;
    }

    public function setProductVariant(?ProductVariantInterface $productVariant): void
    {
        $this->productVariant = $productVariant;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }
}
