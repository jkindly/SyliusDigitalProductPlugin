<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\ProductVariant;

use Sylius\Behat\Page\Admin\ProductVariant\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function changeTab(): void;

    public function nameItInLocale(string $name, string $language): void;
}
