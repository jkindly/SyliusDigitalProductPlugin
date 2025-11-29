<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

use SyliusDigitalProductPlugin\Dto\ExternalUrlFileDto;
use SyliusDigitalProductPlugin\Form\Type\ExternalUrlFileType;

final class ExternalUrlFileProvider implements FileProviderInterface
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
        return ExternalUrlFileType::class;
    }

    public function getDto(): string
    {
        return ExternalUrlFileDto::class;
    }
}
