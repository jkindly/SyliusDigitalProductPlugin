<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

use SyliusDigitalProductPlugin\Form\Type\ExternalUrlDigitalFileType;
use SyliusDigitalProductPlugin\Form\Type\UploadedDigitalFileType;

final class ExternalUrlDigitalFileProvider implements DigitalFileProviderInterface
{
    public const TYPE = 'external_url';

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getLabel(): string
    {
        return 'sylius_digital_product.ui.external_url';
    }

    public function getFormType(): string
    {
        return ExternalUrlDigitalFileType::class;
    }
}
