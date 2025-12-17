<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Shop\Account\Order\ShowPageInterface;
use Webmozart\Assert\Assert;

final class AccountContext implements Context
{
    public function __construct(
        private ShowPageInterface $orderShowPage,
    ) {
    }

    #[Then('I should see digital files table')]
    public function iShouldSeeDigitalFilesTable(): void
    {
        Assert::true(
            $this->orderShowPage->hasDigitalFilesTable(),
            'Digital files table should be visible',
        );
    }

    #[Then('I should not see digital files table')]
    public function iShouldNotSeeDigitalFilesTable(): void
    {
        Assert::false(
            $this->orderShowPage->hasDigitalFilesTable(),
            'Digital files table should not be visible',
        );
    }

    #[Then('I should see :fileName in the digital files table')]
    public function iShouldSeeFileNameInDigitalFilesTable(string $fileName): void
    {
        Assert::true(
            $this->orderShowPage->hasDigitalFile($fileName),
            sprintf('Digital file "%s" should be visible in the table', $fileName),
        );
    }

    #[Then('I should see download button for :fileName')]
    public function iShouldSeeDownloadButtonForFile(string $fileName): void
    {
        Assert::true(
            $this->orderShowPage->hasDownloadButtonForFile($fileName),
            sprintf('Download button for "%s" should be visible', $fileName),
        );
    }

    #[Then('the download button for :fileName should be disabled')]
    public function theDownloadButtonForFileShouldBeDisabled(string $fileName): void
    {
        Assert::true(
            $this->orderShowPage->isDownloadButtonDisabledForFile($fileName),
            sprintf('Download button for "%s" should be disabled', $fileName),
        );
    }

    #[Then('the download button for :fileName should be enabled')]
    public function theDownloadButtonForFileShouldBeEnabled(string $fileName): void
    {
        Assert::false(
            $this->orderShowPage->isDownloadButtonDisabledForFile($fileName),
            sprintf('Download button for "%s" should be enabled', $fileName),
        );
    }
}
