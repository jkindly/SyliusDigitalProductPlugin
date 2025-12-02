<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Channel;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function setDownloadLimitPerCustomer(?string $limit): void;

    public function setDaysAvailableAfterPurchase(?string $daysAvailable): void;

    public function setHiddenQuantity(bool $hidden): void;

    public function isOptionChecked(string $optionName): bool;
}
