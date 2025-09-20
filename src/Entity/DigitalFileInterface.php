<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\PositionAwareInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Resource\Model\TimestampableInterface;

interface DigitalFileInterface extends
    PositionAwareInterface,
    TimestampableInterface
{
    public function getId(): ?int;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): void;
}
