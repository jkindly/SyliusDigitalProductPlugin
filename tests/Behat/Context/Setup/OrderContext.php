<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderInterface;
use Webmozart\Assert\Assert;

final class OrderContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Given('this order has digital files')]
    public function thisOrderHasDigitalFiles(): void
    {
        /** @var OrderInterface $order */
        $order = $this->sharedStorage->get('order');

        Assert::isInstanceOf($order, DigitalProductOrderInterface::class);
        Assert::true($order->hasAnyFile(), 'Order does not have any digital files');
    }

    #[Given('the download limit for :fileName in this order has been reached')]
    public function theDownloadLimitForFileInThisOrderHasBeenReached(string $fileName): void
    {
        /** @var OrderInterface $order */
        $order = $this->sharedStorage->get('order');

        Assert::isInstanceOf($order, DigitalProductOrderInterface::class);

        $this->entityManager->refresh($order);

        foreach ($order->getItems() as $item) {
            foreach ($item->getFiles() as $file) {
                if ($fileName === $file->getName()) {
                    $downloadLimit = $file->getDownloadLimit();
                    Assert::notNull($downloadLimit, sprintf('Download limit for file "%s" is not set', $fileName));

                    for ($i = 0; $i < $downloadLimit; ++$i) {
                        $file->incrementDownloadCount();
                    }

                    $this->entityManager->flush();

                    return;
                }
            }
        }

        throw new \RuntimeException(sprintf('File "%s" not found in order', $fileName));
    }
}
