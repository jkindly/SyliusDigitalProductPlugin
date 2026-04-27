<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener\Workflow\OrderPayment;

use Sylius\Component\Core\Model\OrderInterface;
use SyliusDigitalProductPlugin\Mailer\DigitalDownloadEmailManagerInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class SendDigitalDownloadEmailListener
{
    public function __construct(
        private DigitalDownloadEmailManagerInterface $digitalDownloadEmailManager,
    ) {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->digitalDownloadEmailManager->sendDigitalDownloadEmail($order);
    }
}
