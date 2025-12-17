<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductFile;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Provider\ExternalUrlFileProvider;
use Webmozart\Assert\Assert;

final class ProductContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private ProductVariantResolverInterface $productVariantResolver,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Given('the :product product has digital files')]
    public function theProductHasDigitalFiles(ProductInterface $product): void
    {
        $this->addDigitalFilesToProduct($product, null);
    }

    #[Given('the :product product has digital files with download limit :limit')]
    public function theProductHasDigitalFilesWithDownloadLimit(ProductInterface $product, int $limit): void
    {
        $this->addDigitalFilesToProduct($product, $limit);
    }

    private function addDigitalFilesToProduct(ProductInterface $product, ?int $downloadLimit): void
    {
        $variant = $this->productVariantResolver->getVariant($product);
        Assert::isInstanceOf($variant, DigitalProductVariantInterface::class);

        $channel = $this->sharedStorage->get('channel');
        Assert::isInstanceOf($channel, DigitalProductChannelInterface::class);

        if (null !== $downloadLimit) {
            $channelSettings = $channel->getDigitalProductFileChannelSettings();
            if (null === $channelSettings) {
                $channelSettings = new DigitalProductChannelSettings();
                $channelSettings->setChannel($channel);
                $channel->setDigitalProductFileChannelSettings($channelSettings);
                $this->entityManager->persist($channelSettings);
            }
            $channelSettings->setDownloadLimit($downloadLimit);
        }

        $file = new DigitalProductFile();
        $file->setName('Sample Digital File');
        $file->setType(ExternalUrlFileProvider::TYPE);
        $file->setConfiguration(['url' => 'https://example.com/file.pdf']);
        $file->setChannel($channel);
        $file->setProductVariant($variant);

        $variant->addFile($file);

        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }
}
