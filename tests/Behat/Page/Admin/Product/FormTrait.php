<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Product;

use Sylius\Behat\Service\DriverHelper;
use Sylius\Component\Core\Model\ChannelInterface;

trait FormTrait
{
    public function changeTab(): void
    {
        if (DriverHelper::isNotJavascript($this->getDriver())) {
            return;
        }

        $this->getElement('side_navigation_tab', ['%name%' => 'files'])->click();
    }

    public function getFieldValue(string $field, ?ChannelInterface $channel = null): string
    {
        $value = $this->getDocument()->findField($field)?->getValue();

        if (null !== $channel) {
            $parent = $this->getDocument()->findById(sprintf('digital-files-%s', $channel->getCode()));
            $value = $parent->findField($field)?->getValue();
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Field "%s" not found.', $field));
        }

        return $value;
    }

    public function checkField(string $field): void
    {
        $checkbox = $this->getDocument()->findField($field);
        if (null === $checkbox) {
            throw new \InvalidArgumentException(sprintf('Checkbox "%s" not found.', $field));
        }

        if (!$checkbox->isChecked()) {
            $checkbox->check();
        }
    }

    public function getDefinedFormElements(): array
    {
        return [
            'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]',
            'channel_accordion' => '[data-test-digital-product-files-accordion="%channel_code%"]',
        ];
    }
}
