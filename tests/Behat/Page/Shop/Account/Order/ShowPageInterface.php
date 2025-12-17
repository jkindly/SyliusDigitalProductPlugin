<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Shop\Account\Order;

use Sylius\Behat\Page\Shop\Account\Order\ShowPageInterface as BaseShowPageInterface;

interface ShowPageInterface extends BaseShowPageInterface
{
    public function hasDigitalFilesTable(): bool;

    public function hasDigitalFile(string $fileName): bool;

    public function hasDownloadButtonForFile(string $fileName): bool;

    public function isDownloadButtonDisabledForFile(string $fileName): bool;
}
