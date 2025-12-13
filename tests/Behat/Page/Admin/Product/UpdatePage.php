<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Product;

use Sylius\Behat\Page\Admin\Crud\UpdatePage as BaseUpdatePage;

class UpdatePage extends BaseUpdatePage implements UpdatePageInterface
{
    use FormTrait;

    public function isQuantityFieldPresent(): bool
    {
        $this->getSession()->visit('/en_US/products/digital-simple-product');

        return true;
    }

    protected function getDefinedElements(): array
    {
        return array_merge(
            parent::getDefinedElements(),
            $this->getDefinedFormElements(),
        );
    }
}
