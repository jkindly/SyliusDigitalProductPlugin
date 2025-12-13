<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\ProductVariant;

use Sylius\Behat\Page\Admin\ProductVariant\IndexPage as BaseIndexPage;

final class IndexPage extends BaseIndexPage implements IndexPageInterface
{
    use FormTrait;

    public function goToEditPage(): void
    {
        $this->getElement('variant_edit_page')->click();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), $this->getDefinedFormElements());
    }
}
