<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

use SyliusDigitalProductPlugin\Form\Type\UploadedFileType;

final class ExternalUrlDigitalFileProvider implements DigitalFileProviderInterface
{
    public function getType(): string
    {
        return 'external_url';
    }

    public function getLabel(): string
    {
        return 'sylius_digital_product.ui.external_url';
    }

    public function getFormType(): string
    {
        return UploadedFileType::class;
    }
}
