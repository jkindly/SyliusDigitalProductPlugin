<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
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
        return 'sylius_digital_product.ui.uploaded_file.title';
    }

    public function getFormType(): string
    {
        return UploadedDigitalFileType::class;
    }

    public function getDto(): string
    {
        return UploadedDigitalFileDto::class;
    }
}
