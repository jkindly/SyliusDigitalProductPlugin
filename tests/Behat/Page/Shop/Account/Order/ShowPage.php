<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Page\Shop\Account\Order;

use Sylius\Behat\Page\Shop\Account\Order\ShowPage as BaseShowPage;

final class ShowPage extends BaseShowPage implements ShowPageInterface
{
    public function hasDigitalFilesTable(): bool
    {
        return $this->hasElement('digital_files_table');
    }

    public function hasDigitalFile(string $fileName): bool
    {
        $table = $this->getElement('digital_files_table');
        $rows = $table->findAll('css', 'tbody tr');

        foreach ($rows as $row) {
            $nameCell = $row->find('css', 'td:first-child');
            if (null !== $nameCell && str_contains($nameCell->getText(), $fileName)) {
                return true;
            }
        }

        return false;
    }

    public function hasDownloadButtonForFile(string $fileName): bool
    {
        $table = $this->getElement('digital_files_table');
        $rows = $table->findAll('css', 'tbody tr');

        foreach ($rows as $row) {
            $nameCell = $row->find('css', 'td:first-child');
            if (null !== $nameCell && str_contains($nameCell->getText(), $fileName)) {
                $downloadButton = $row->find('css', 'td:last-child a.btn-primary');

                return null !== $downloadButton;
            }
        }

        return false;
    }

    public function isDownloadButtonDisabledForFile(string $fileName): bool
    {
        $table = $this->getElement('digital_files_table');
        $rows = $table->findAll('css', 'tbody tr');

        foreach ($rows as $row) {
            $nameCell = $row->find('css', 'td:first-child');
            if (null !== $nameCell && str_contains($nameCell->getText(), $fileName)) {
                $disabledButton = $row->find('css', 'td:last-child button.btn-danger.disabled');

                return null !== $disabledButton;
            }
        }

        return false;
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'digital_files_table' => '[data-test-order-table-digital-files]',
        ]);
    }
}
