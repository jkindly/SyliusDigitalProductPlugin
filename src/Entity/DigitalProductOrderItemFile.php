<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Resource\Model\TimestampableTrait;
use Symfony\Component\Uid\Uuid;

final class DigitalProductOrderItemFile implements DigitalProductOrderItemFileInterface
{
    use TimestampableTrait;

    protected ?int $id = null;

    protected string $uuid;

    protected ?string $name = null;

    protected ?string $type = null;

    protected int $downloadCount = 0;

    protected ?int $downloadLimit = null;

    protected ?OrderItemInterface $orderItem = null;

    protected array $configuration = [];

    protected ?\DateTimeInterface $availableUntil = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getDownloadCount(): int
    {
        return $this->downloadCount;
    }

    public function setDownloadCount(int $downloadCount): void
    {
        $this->downloadCount = $downloadCount;
    }

    public function getDownloadLimit(): ?int
    {
        return $this->downloadLimit;
    }

    public function setDownloadLimit(?int $downloadLimit): void
    {
        $this->downloadLimit = $downloadLimit;
    }

    public function incrementDownloadCount(): void
    {
        ++$this->downloadCount;
    }

    public function getRemainingDownloadCount(): ?int
    {
        if (null === $this->downloadLimit) {
            return null;
        }

        return $this->downloadLimit - $this->downloadCount;
    }

    public function getOrderItem(): ?OrderItemInterface
    {
        return $this->orderItem;
    }

    public function setOrderItem(?OrderItemInterface $orderItem): void
    {
        $this->orderItem = $orderItem;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getAvailableUntil(): ?\DateTimeInterface
    {
        return $this->availableUntil;
    }

    public function setAvailableUntil(?\DateTimeInterface $availableUntil): void
    {
        $this->availableUntil = $availableUntil;
    }

    public function isAvailable(): bool
    {
        if (null === $this->availableUntil) {
            return true;
        }

        return $this->availableUntil >= new \DateTime();
    }
}
