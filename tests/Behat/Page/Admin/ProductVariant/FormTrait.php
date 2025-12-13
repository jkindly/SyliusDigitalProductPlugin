<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\ProductVariant;

use Sylius\Behat\Service\DriverHelper;

trait FormTrait
{
    public function changeTab(): void
    {
        if (DriverHelper::isNotJavascript($this->getDriver())) {
            return;
        }

        $this->getElement('side_navigation_tab', ['%name%' => 'files'])->click();
    }

    public function getDefinedFormElements(): array
    {
        return [
            'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]',
            'translation_accordion' => '[data-test-translations-accordion="%locale_code%"]',
            'name' => '[data-test-name]',
            'variant_edit_page' => '[data-test-actions] a[href$="/edit"]',
        ];
    }
}
