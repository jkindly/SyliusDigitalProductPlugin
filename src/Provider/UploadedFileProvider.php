<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Provider;

use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Form\Type\UploadedFileType;

final class UploadedFileProvider implements FileProviderInterface
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
        return UploadedFileType::class;
    }

    public function getDto(): string
    {
        return UploadedFileDto::class;
    }
}
