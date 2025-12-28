<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Product\ChannelPricingsFormElementInterface;
use Sylius\Behat\Page\Shop\Product\IndexPageInterface as ShopProductIndexPageInterface;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Element\Admin\Product\DigitalFilesFormElementInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\ProductVariant\CreatePageInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\ProductVariant\IndexPageInterface;
use Tests\SyliusDigitalProductPlugin\Behat\Page\Admin\ProductVariant\UpdatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingProductVariantsContext implements Context
{
    public function __construct(
        private readonly SharedStorageInterface $sharedStorage,
        private readonly CreatePageInterface $createPage,
        private readonly UpdatePageInterface $updatePage,
        private readonly IndexPageInterface $indexPage,
        private readonly CurrentPageResolverInterface $currentPageResolver,
        private readonly DigitalFilesFormElementInterface $digitalFilesFormElement,
        private readonly ShopProductIndexPageInterface $shopProductIndexPage,
        private readonly ChannelPricingsFormElementInterface $channelPricingsFormElement,
    ) {
    }

    #[When('I name it :name in :language language')]
    public function iNameItIn(string $name, string $language): void
    {
        $this->createPage->nameItInLocale($name, $language);
    }

    #[When('I set its slug to :slug')]
    public function iSetItsSlugTo(string $slug): void
    {
        $this->createPage->getDocument()->fillField('Slug', $slug);
    }

    #[When('I make it available in channel :channel')]
    public function iMakeItAvailableInChannel(ChannelInterface $channel): void
    {
        $this->createPage->getDocument()->checkField(sprintf('sylius_admin_product_variant_channelPricings_%s_enabled', $channel->getCode()));
    }

    #[When('I open the Digital section')]
    public function iOpenTheDigitalSection(): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $currentPage = $this->currentPageResolver->getCurrentPageWithForm([$this->createPage, $this->updatePage]);
        $currentPage->changeTab();
    }

    #[When('I set its price to :price for channel :channel')]
    public function iSetItsPriceTo(string $price, ChannelInterface $channel): void
    {
        $this->channelPricingsFormElement->specifyPrice($channel, $price);
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

    #[Then('the product :name should appear in the store')]
    public function theProductShouldAppearInTheStore(string $name): void
    {
        $this->shopProductIndexPage->open();

        Assert::true(
            $this->shopProductIndexPage->isProductOnList($name),
            sprintf('Product %s should appear in the store', $name),
        );
    }

    #[When('I go to edit page of variant created')]
    public function iGoToEditPageOfVariantCreated(): void
    {
        $this->indexPage->goToEditPage();
    }
}
