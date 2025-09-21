<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

use SyliusDigitalProductPlugin\Form\Type\UploadedDigitalFileType;

final class UploadedDigitalFileProvider implements DigitalFileProviderInterface
{
    public const TYPE = 'uploaded_file';

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getLabel(): string
    {
        return 'sylius_digital_product.ui.uploaded_file';
    }

    public function getFormType(): string
    {
        return UploadedDigitalFileType::class;
    }
}
