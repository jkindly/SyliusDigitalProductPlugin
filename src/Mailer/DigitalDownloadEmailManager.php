<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Mailer;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use SyliusDigitalProductPlugin\Repository\DigitalProductOrderItemFileRepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final readonly class DigitalDownloadEmailManager implements DigitalDownloadEmailManagerInterface
{
    public function __construct(
        private SenderInterface $emailSender,
        private DigitalProductOrderItemFileRepositoryInterface $orderItemFileRepository,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendDigitalDownloadEmail(OrderInterface $order): void
    {
        $files = $this->orderItemFileRepository->findByOrder($order);
        if ([] === $files) {
            return;
        }

        $customer = $order->getCustomer();
        Assert::notNull($customer);

        $recipientEmail = $customer->getEmail();
        Assert::notNull($recipientEmail);

        $downloadLinks = [];
        foreach ($files as $file) {
            $downloadLinks[] = [
                'name' => $file->getName(),
                'url' => $this->urlGenerator->generate(
                    'sylius_digital_product_shop_download_order_item_file',
                    ['uuid' => $file->getUuid()],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                'availableUntil' => $file->getAvailableUntil(),
                'downloadLimit' => $file->getDownloadLimit(),
            ];
        }

        $this->emailSender->send(
            Emails::DIGITAL_DOWNLOAD,
            [$recipientEmail],
            [
                'order' => $order,
                'channel' => $order->getChannel(),
                'localeCode' => $order->getLocaleCode(),
                'download_links' => $downloadLinks,
            ],
        );
    }
}
