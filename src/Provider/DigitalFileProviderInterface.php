<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

interface DigitalFileProviderInterface
{
    public function getType(): string;

    public function getLabel(): string;

    /**
     * FQCN of the form type.
     */
    public function getFormType(): string;

    /**
     * FQCN of the DTO.
     */
    public function getDto(): string;
}
