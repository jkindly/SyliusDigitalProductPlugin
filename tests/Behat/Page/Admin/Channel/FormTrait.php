<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Channel;

trait FormTrait
{
    public function getDefinedFormElements(): array
    {
        return [
            'download_limit' => '[data-test-download-limit]',
            'days_available' => '[data-test-days-available]',
            'hidden_quantity' => '[data-test-hidden-quantity]',
        ];
    }

    public function friendlyNameToTestName(string $name): string
    {
        return match ($name) {
            'download limit per customer' => 'download_limit',
            'days available after purchase' => 'days_available',
            'hide quantity on product page' => 'hidden_quantity',
            default => $name,
        };
    }
}
