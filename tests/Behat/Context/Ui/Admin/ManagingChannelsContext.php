<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Channel\CreatePageInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Channel\FormTrait;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Channel\UpdatePageInterface;
use Webmozart\Assert\Assert;

final readonly class ManagingChannelsContext implements Context
{
    use FormTrait;

    public function __construct(
        private CreatePageInterface $createPage,
        private UpdatePageInterface $updatePage,
        private CurrentPageResolverInterface $currentPageResolver,
    ) {
    }

    /**
     * @When I set download limit per customer to :limit
     */
    public function iSetDownloadLimit(?string $limit = null): void
    {
        $this->updatePage->setDownloadLimitPerCustomer($limit);
    }

    /**
     * @When I set days available after purchase to :days
     */
    public function iSetDaysAvailableAfterPurchase(?string $days = null): void
    {
        $this->updatePage->setDaysAvailableAfterPurchase($days);
    }

    /**
     * @When I check hide quantity on product page
     */
    public function iCheckHideQuantityOnProductPage(): void
    {
        $this->updatePage->setHiddenQuantity(true);
    }

    /**
     * @When I uncheck hide quantity on product page
     */
    public function iUncheckHideQuantityOnProductPage(): void
    {
        $this->updatePage->setHiddenQuantity(false);
    }

    /**
     * @Then I should see value :value in :field field
     */
    public function iShouldSeeValueInField(string $value, string $field): void
    {
        $field = $this->friendlyNameToTestName(mb_lcfirst($field));

        Assert::true($this->updatePage->hasResourceValues([$field => $value]));
    }

    /**
     * @Then the :option option should be checked
     */
    public function theOptionShouldBeChecked(string $option): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $currentPage = $this->currentPageResolver->getCurrentPageWithForm([$this->createPage, $this->updatePage]);

        Assert::true($currentPage->isOptionChecked($this->friendlyNameToTestName(mb_lcfirst($option))));
    }

    /**
     * @Then I should be notified that :field should be positive
     */
    public function iShouldBeNotifiedThatFieldShouldBePositive(string $field): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $currentPage = $this->currentPageResolver->getCurrentPageWithForm([$this->createPage, $this->updatePage]);
        $field = $this->friendlyNameToTestName(mb_lcfirst($field));

        Assert::same($currentPage->getValidationMessage($field), 'This value should be positive.');
    }
}
