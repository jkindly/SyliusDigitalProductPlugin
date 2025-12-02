<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Channel;

use Sylius\Behat\Page\Admin\Crud\UpdatePage as BaseUpdatePage;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Channel\FormTrait;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Channel\UpdatePageInterface;

class UpdatePage extends BaseUpdatePage implements UpdatePageInterface
{
    use FormTrait;

    public function setDownloadLimitPerCustomer(?string $limit): void
    {
        $this->getElement('download_limit')->setValue($limit ?? '');
    }

    public function setDaysAvailableAfterPurchase(?string $daysAvailable): void
    {
        $this->getElement('days_available')->setValue($daysAvailable ?? '');
    }

    public function setHiddenQuantity(bool $hidden): void
    {
        if ($hidden) {
            $this->getElement('hidden_quantity')->check();
        } else {
            $this->getElement('hidden_quantity')->uncheck();
        }
    }

    public function isOptionChecked(string $optionName): bool
    {
        return $this->getElement($optionName)->isChecked();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(
            parent::getDefinedElements(),
            $this->getDefinedFormElements(),
        );
    }
}
