<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Context\Ui\Shop;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Step\When;
use Sylius\Behat\Service\SharedStorageInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Shop\Account\Order\ShowPageInterface;

final class DownloadContext implements MinkAwareContext
{
    private Mink $mink;

    private array $minkParameters;

    public function __construct(
        private ShowPageInterface $orderShowPage,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    public function setMink(Mink $mink): void
    {
        $this->mink = $mink;
    }

    public function setMinkParameters(array $parameters): void
    {
        $this->minkParameters = $parameters;
    }

    #[When('I access the download URL for :fileName')]
    public function iAccessDownloadUrlForFile(string $fileName): void
    {
        $path = $this->sharedStorage->get('download_url_' . $fileName);
        $baseUrl = rtrim($this->minkParameters['base_url'], '/');
        $this->getSession()->visit($baseUrl . $path);
    }

    #[When('I download :fileName from my order')]
    public function iDownloadFileFromMyOrder(string $fileName): void
    {
        $this->orderShowPage->clickDownloadForFile($fileName);
    }

    private function getSession(?string $name = null): Session
    {
        return $this->mink->getSession($name);
    }
}
