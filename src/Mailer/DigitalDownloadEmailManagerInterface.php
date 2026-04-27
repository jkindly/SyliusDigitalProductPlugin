<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Mailer;

use Sylius\Component\Core\Model\OrderInterface;

interface DigitalDownloadEmailManagerInterface
{
    public function sendDigitalDownloadEmail(OrderInterface $order): void;
}
