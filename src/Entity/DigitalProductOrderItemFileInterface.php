<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\OrderItemInterface;

interface DigitalProductOrderItemFileInterface
{
    public function getId(): ?int;

    public function setId(?int $id): void;

    public function getUuid(): string;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getType(): ?string;

    public function setType(?string $type): void;

    public function getDownloadCount(): int;

    public function setDownloadCount(int $downloadCount): void;

    public function getDownloadLimit(): ?int;

    public function setDownloadLimit(?int $downloadLimit): void;

    public function incrementDownloadCount(): void;

    public function getRemainingDownloadCount(): ?int;

    public function getOrderItem(): ?OrderItemInterface;

    public function setOrderItem(?OrderItemInterface $orderItem): void;

    public function getConfiguration(): array;

    public function setConfiguration(array $configuration): void;

    public function getAvailableUntil(): ?\DateTimeInterface;

    public function setAvailableUntil(?\DateTimeInterface $availableUntil): void;

    public function isAvailable(): bool;
}
