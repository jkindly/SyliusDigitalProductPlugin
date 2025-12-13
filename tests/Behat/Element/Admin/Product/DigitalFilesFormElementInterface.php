<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Element\Admin\Product;

use Sylius\Behat\Element\Admin\Crud\FormElementInterface;
use Sylius\Component\Core\Model\ChannelInterface;

interface DigitalFilesFormElementInterface extends FormElementInterface
{
    public function uploadDigitalFile(string $path, string $name): void;

    public function addExternalUrlFile(string $url, string $name): void;

    public function openChannelAccordion(ChannelInterface $channel): void;

    public function hasFileWithName(string $name): bool;

    public function hasUploadedFileDownloadLink(): bool;

    public function enableSettingsForThisProduct(): void;
}
