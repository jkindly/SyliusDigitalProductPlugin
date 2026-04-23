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

    public function setChannelPrice(string $channelCode, string $price): void
    {
        if (DriverHelper::isJavascript($this->getDriver())) {
            $this->getElement('channel_pricing_tab')->click();

            $accordion = $this->getElement('channel_pricing_accordion', ['%channel_code%' => $channelCode]);
            if ($accordion->hasClass('collapsed')) {
                $accordion->click();
            }
        }

        $this->getElement('channel_price', ['%channel_code%' => $channelCode])->setValue($price);
    }

    public function getDefinedFormElements(): array
    {
        return [
            'channel_price' => '#sylius_admin_product_variant_channelPricings_%channel_code%_price',
            'channel_pricing_accordion' => '[data-test-product-channel-pricings-accordion="%channel_code%"]',
            'channel_pricing_tab' => '[data-test-side-navigation-tab="channel-pricing"]',
            'name' => '[data-test-name]',
            'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]',
            'translation_accordion' => '[data-test-translations-accordion="%locale_code%"]',
            'variant_edit_page' => '[data-test-actions] a[href$="/edit"]',
        ];
    }
}
