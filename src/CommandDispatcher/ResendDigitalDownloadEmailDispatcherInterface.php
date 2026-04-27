<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\CommandDispatcher;

use Sylius\Component\Core\Model\OrderInterface;

interface ResendDigitalDownloadEmailDispatcherInterface
{
    public function dispatch(OrderInterface $order): void;
}
