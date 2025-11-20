<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariant;

interface DigitalProductVariantSettingsInterface extends SettingsInterface
{
    public const CONFIGURATION_HIDDEN_QUANTITY = 'hiddenQuantity';

    public function getId(): ?int;

    public function getProductVariant(): ?ProductVariant;

    public function setProductVariant(?ProductVariant $productVariant): void;

    public function getConfiguration(): array;

    public function setConfiguration(array $configuration): void;
}
