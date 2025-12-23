<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Entity;

interface DigitalProductFileBaseInterface
{
    public function getId(): ?int;

    public function getUuid(): string;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getType(): ?string;

    public function setType(?string $type): void;

    public function getConfiguration(): array;

    public function setConfiguration(array $configuration): void;
}
