<?php

declare(strict_types=1);

namespace Tests\Jkindly\SyliusDigitalProductPlugin\Behat\Page\Admin\Product;

use Sylius\Behat\Page\Admin\Crud\UpdatePageInterface as BaseUpdatePageInterface;

interface UpdatePageInterface extends BaseUpdatePageInterface
{
    public function isQuantityFieldPresent(): bool;
}
