<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Model\TimestampableInterface;

interface DigitalProductFileInterface extends TimestampableInterface
{
    public function getId(): ?int;

    public function getUuid(): string;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getType(): ?string;

    public function setType(?string $type): void;

    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;

    public function getProductVariant(): ?ProductVariantInterface;

    public function setProductVariant(?ProductVariantInterface $productVariant): void;

    public function getConfiguration(): array;

    public function setConfiguration(array $configuration): void;

    public function getSettings(): ?DigitalProductFileOwnedSettingsInterface;

    public function setSettings(?DigitalProductFileOwnedSettingsInterface $settings): void;
}
