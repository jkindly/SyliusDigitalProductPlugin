<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\CommandDispatcher;

use Sylius\Component\Core\Model\OrderInterface;
use SyliusDigitalProductPlugin\Command\SendDigitalDownloadEmail;
use Symfony\Component\Messenger\MessageBusInterface;

final class ResendDigitalDownloadEmailDispatcher implements ResendDigitalDownloadEmailDispatcherInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function dispatch(OrderInterface $order): void
    {
        $orderTokenValue = $order->getTokenValue();
        if (null === $orderTokenValue) {
            throw new \InvalidArgumentException('Order token value cannot be null.');
        }

        $this->messageBus->dispatch(new SendDigitalDownloadEmail($orderTokenValue));
    }
}
