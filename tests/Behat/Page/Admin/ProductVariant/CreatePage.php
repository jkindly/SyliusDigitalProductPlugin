<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\ProductVariant;

use Sylius\Behat\Page\Admin\ProductVariant\CreatePage as BaseCreatePage;

final class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    use FormTrait;

    public function nameItInLocale(string $name, string $language): void
    {
        $this->getElement('side_navigation_tab', ['%name%' => 'translations'])->click();
        $this->getElement('name')->setValue($name);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), $this->getDefinedFormElements());
    }
}
