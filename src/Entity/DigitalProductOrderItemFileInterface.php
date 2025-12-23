<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\OrderItemInterface;

interface DigitalProductOrderItemFileInterface extends DigitalProductFileBaseInterface
{
    public function getDownloadCount(): int;

    public function setDownloadCount(int $downloadCount): void;

    public function getDownloadLimit(): ?int;

    public function setDownloadLimit(?int $downloadLimit): void;

    public function incrementDownloadCount(): void;

    public function getRemainingDownloadCount(): ?int;

    public function getOrderItem(): ?OrderItemInterface;

    public function setOrderItem(?OrderItemInterface $orderItem): void;

    public function getAvailableUntil(): ?\DateTimeInterface;

    public function setAvailableUntil(?\DateTimeInterface $availableUntil): void;

    public function isAvailable(): bool;
}
