<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

use SyliusDigitalProductPlugin\Dto\ExternalUrlDigitalFileDto;
use SyliusDigitalProductPlugin\Form\Type\ExternalUrlDigitalFileType;

final class ExternalUrlDigitalFileProvider implements DigitalFileProviderInterface
{
    public const TYPE = 'external_url';

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getLabel(): string
    {
        return 'sylius_digital_product.ui.external_url.title';
    }

    public function getFormType(): string
    {
        return ExternalUrlDigitalFileType::class;
    }

    public function getDto(): string
    {
        return ExternalUrlDigitalFileDto::class;
    }
}
