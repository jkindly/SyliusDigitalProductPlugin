<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Page\Shop\Product\ShowPageInterface;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Element\Admin\Product\DigitalFilesFormElementInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Product\CreatePageInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\Product\UpdatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingProductsContext implements Context
{
    public function __construct(
        private CreatePageInterface $createPage,
        private UpdatePageInterface $updatePage,
        private ShowPageInterface $productShowPage,
        private CurrentPageResolverInterface $currentPageResolver,
        private DigitalFilesFormElementInterface $digitalFilesFormElement,
    ) {
    }

    #[When('I open the Digital section')]
    public function iOpenTheDigitalSection():  void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $currentPage = $this->currentPageResolver->getCurrentPageWithForm([$this->createPage, $this->updatePage]);
        $currentPage->changeTab();
    }

    #[When('I select accordion for :channel channel')]
    public function iSelectAccordionForChannel(ChannelInterface $channel): void
    {
        $this->digitalFilesFormElement->openChannelAccordion($channel);
    }

    #[When('I upload a digital file :path with name :name')]
    public function iUploadADigitalFileWithName(string $path, string $name): void
    {
        $this->digitalFilesFormElement->uploadDigitalFile($path, $name);
    }

    #[When('I add an external URL file :url with name :name')]
    public function iAddAnExternalUrlFileWithName(string $url, string $name): void
    {
        $this->digitalFilesFormElement->addExternalUrlFile($url, $name);
    }

    #[Then('the digital file :name should be listed in the :channel channel accordion')]
    public function theDigitalFileShouldBeListedInTheChannelAccordion(string $name, ChannelInterface $channel): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $currentPage = $this->currentPageResolver->getCurrentPageWithForm([$this->createPage, $this->updatePage]);
        $currentPage->changeTab();

        $this->digitalFilesFormElement->openChannelAccordion($channel);

        Assert::true(
            $this->digitalFilesFormElement->hasFileWithName($name),
            sprintf('Digital file "%s" should be listed in the channel accordion', $name),
        );
    }

    #[Then('the uploaded file should have a download link')]
    public function theUploadedFileShouldHaveADownloadLink(): void
    {
        Assert::true(
            $this->digitalFilesFormElement->hasUploadedFileDownloadLink(),
            'Uploaded file should have a download link',
        );
    }

    #[Then('I should see :value in :field field in :channel channel accordion')]
    #[Then('I should see :value in :field field')]
    public function iShouldSeeValueInField(string $value, string $field, ?ChannelInterface $channel = null): void
    {
        if (null !== $channel) {
            $this->digitalFilesFormElement->openChannelAccordion($channel);
        }

        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $currentPage = $this->currentPageResolver->getCurrentPageWithForm([$this->createPage, $this->updatePage]);
        Assert::eq($value, $currentPage->getFieldValue($field, $channel));
    }

    #[When('I enable settings for this product')]
    public function iEnableSettingsForThisProduct(): void
    {
        $this->digitalFilesFormElement->enableSettingsForThisProduct();
    }

    #[When('I check :field field')]
    public function iCheckField(string $field): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $currentPage = $this->currentPageResolver->getCurrentPageWithForm([$this->createPage, $this->updatePage]);
        $currentPage->checkField($field);
    }

    #[Then('I should see quantity on product page')]
    public function iShouldSeeQuantityOnProductPage(): void
    {
        $this->productShowPage->updateQuantity(1);
    }

    #[Then('I should not see quantity on product page')]
    public function iShouldNotSeeQuantityOnProductPage(): void
    {
        try {
            $this->productShowPage->updateQuantity(0);

            throw new \RuntimeException('Quantity field is present on product page but it should not be.');
        } catch (ElementNotFoundException) {
        }
    }

    /**
     * @When I check :product product details in the :channel channel and :locale locale
     */
    public function iOpenProductPage(ProductInterface $product, ChannelInterface $channel, string $locale = 'en_US'): void
    {
        $this->productShowPage->open([
            'slug' => $product->getTranslation($locale)->getSlug(),
            '_channel_code' => $channel->getCode(),
            '_locale' => $locale,
        ]);
    }}
