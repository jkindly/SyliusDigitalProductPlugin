<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Product;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    use FormTrait;

    protected function getDefinedElements(): array
    {
        return array_merge(
            parent::getDefinedElements(),
            $this->getDefinedFormElements(),
        );
    }
}
