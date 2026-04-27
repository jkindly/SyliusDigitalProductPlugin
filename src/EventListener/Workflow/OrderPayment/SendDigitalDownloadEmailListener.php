<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener\Workflow\OrderPayment;

use Sylius\Component\Core\Model\OrderInterface;
use SyliusDigitalProductPlugin\CommandDispatcher\ResendDigitalDownloadEmailDispatcherInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class SendDigitalDownloadEmailListener
{
    public function __construct(
        private ResendDigitalDownloadEmailDispatcherInterface $digitalDownloadEmailDispatcher,
    ) {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->digitalDownloadEmailDispatcher->dispatch($order);
    }
}
