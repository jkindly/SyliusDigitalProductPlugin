<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\CommandHandler;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use SyliusDigitalProductPlugin\Command\SendDigitalDownloadEmail;
use SyliusDigitalProductPlugin\Mailer\DigitalDownloadEmailManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendDigitalDownloadEmailHandler
{
    /** @param OrderRepositoryInterface<OrderInterface> $orderRepository */
    public function __construct(
        private DigitalDownloadEmailManagerInterface $digitalDownloadEmailManager,
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function __invoke(SendDigitalDownloadEmail $command): void
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $command->orderTokenValue]);
        if (null === $order) {
            throw new NotFoundHttpException(sprintf('The order with tokenValue %s has not been found', $command->orderTokenValue));
        }

        $this->digitalDownloadEmailManager->sendDigitalDownloadEmail($order);
    }
}
