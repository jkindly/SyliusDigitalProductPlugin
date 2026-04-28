<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Mailer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use SyliusDigitalProductPlugin\Mailer\DigitalDownloadEmailManager;
use SyliusDigitalProductPlugin\Mailer\Emails;
use SyliusDigitalProductPlugin\Repository\DigitalProductOrderItemFileRepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DigitalDownloadEmailManagerTest extends TestCase
{
    private MockObject&SenderInterface $emailSender;

    private MockObject&DigitalProductOrderItemFileRepositoryInterface $orderItemFileRepository;

    private MockObject&UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        $this->emailSender = $this->createMock(SenderInterface::class);
        $this->orderItemFileRepository = $this->createMock(DigitalProductOrderItemFileRepositoryInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
    }

    private function createManager(): DigitalDownloadEmailManager
    {
        return new DigitalDownloadEmailManager(
            $this->emailSender,
            $this->orderItemFileRepository,
            $this->urlGenerator,
        );
    }

    private function createOrder(string $email, array $files): MockObject&OrderInterface
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn($email);

        $channel = $this->createMock(ChannelInterface::class);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getChannel')->willReturn($channel);
        $order->method('getLocaleCode')->willReturn('en_US');

        $this->orderItemFileRepository
            ->method('findByOrder')
            ->with($order)
            ->willReturn($files);

        return $order;
    }

    public function testSendsEmailWithDownloadLinksWhenFilesExist(): void
    {
        $availableUntil = new \DateTimeImmutable('2025-12-31');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getUuid')->willReturn('test-uuid');
        $file->method('getName')->willReturn('Invoice.pdf');
        $file->method('getAvailableUntil')->willReturn($availableUntil);
        $file->method('getDownloadLimit')->willReturn(5);

        $order = $this->createOrder('customer@example.com', [$file]);

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                'sylius_digital_product_shop_download_order_item_file',
                ['uuid' => 'test-uuid'],
                UrlGeneratorInterface::ABSOLUTE_URL,
            )
            ->willReturn('https://example.com/download/test-uuid');

        $this->emailSender
            ->expects($this->once())
            ->method('send')
            ->with(
                Emails::DIGITAL_DOWNLOAD,
                ['customer@example.com'],
                $this->callback(function (array $context) use ($order, $availableUntil): bool {
                    return $context['order'] === $order
                        && 'en_US' === $context['localeCode']
                        && [
                            [
                                'name' => 'Invoice.pdf',
                                'url' => 'https://example.com/download/test-uuid',
                                'availableUntil' => $availableUntil,
                                'downloadLimit' => 5,
                            ],
                        ] === $context['download_links'];
                }),
            );

        $this->createManager()->sendDigitalDownloadEmail($order);
    }

    public function testDoesNotSendEmailWhenNoFilesExist(): void
    {
        $order = $this->createOrder('customer@example.com', []);

        $this->emailSender->expects($this->never())->method('send');
        $this->urlGenerator->expects($this->never())->method('generate');

        $this->createManager()->sendDigitalDownloadEmail($order);
    }

    public function testSendsOneUrlPerFile(): void
    {
        $file1 = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file1->method('getUuid')->willReturn('uuid-1');
        $file1->method('getName')->willReturn('File 1');
        $file1->method('getAvailableUntil')->willReturn(null);
        $file1->method('getDownloadLimit')->willReturn(null);

        $file2 = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file2->method('getUuid')->willReturn('uuid-2');
        $file2->method('getName')->willReturn('File 2');
        $file2->method('getAvailableUntil')->willReturn(null);
        $file2->method('getDownloadLimit')->willReturn(null);

        $order = $this->createOrder('customer@example.com', [$file1, $file2]);

        $this->urlGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                'https://example.com/download/uuid-1',
                'https://example.com/download/uuid-2',
            );

        $this->emailSender
            ->expects($this->once())
            ->method('send')
            ->with(
                Emails::DIGITAL_DOWNLOAD,
                ['customer@example.com'],
                $this->callback(fn (array $context): bool => 2 === count($context['download_links'])),
            );

        $this->createManager()->sendDigitalDownloadEmail($order);
    }

    public function testPassesOrderChannelAndLocaleCodeToEmailContext(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn('customer@example.com');

        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getUuid')->willReturn('uuid-1');
        $file->method('getName')->willReturn('File');
        $file->method('getAvailableUntil')->willReturn(null);
        $file->method('getDownloadLimit')->willReturn(null);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getChannel')->willReturn($channel);
        $order->method('getLocaleCode')->willReturn('fr_FR');

        $this->orderItemFileRepository->method('findByOrder')->willReturn([$file]);
        $this->urlGenerator->method('generate')->willReturn('https://example.com/download/uuid-1');

        $this->emailSender
            ->expects($this->once())
            ->method('send')
            ->with(
                Emails::DIGITAL_DOWNLOAD,
                ['customer@example.com'],
                $this->callback(function (array $context) use ($order, $channel): bool {
                    return $context['order'] === $order
                        && $context['channel'] === $channel
                        && 'fr_FR' === $context['localeCode'];
                }),
            );

        $this->createManager()->sendDigitalDownloadEmail($order);
    }

    public function testThrowsWhenCustomerIsNull(): void
    {
        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn(null);

        $this->orderItemFileRepository->method('findByOrder')->willReturn([$file]);

        $this->expectException(\InvalidArgumentException::class);

        $this->createManager()->sendDigitalDownloadEmail($order);
    }

    public function testThrowsWhenCustomerEmailIsNull(): void
    {
        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn(null);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $this->orderItemFileRepository->method('findByOrder')->willReturn([$file]);

        $this->expectException(\InvalidArgumentException::class);

        $this->createManager()->sendDigitalDownloadEmail($order);
    }

    public function testGeneratesAbsoluteUrls(): void
    {
        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getUuid')->willReturn('uuid-1');
        $file->method('getName')->willReturn('File');
        $file->method('getAvailableUntil')->willReturn(null);
        $file->method('getDownloadLimit')->willReturn(null);

        $order = $this->createOrder('customer@example.com', [$file]);

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->anything(),
                $this->anything(),
                UrlGeneratorInterface::ABSOLUTE_URL,
            )
            ->willReturn('https://example.com/download/uuid-1');

        $this->emailSender->method('send');

        $this->createManager()->sendDigitalDownloadEmail($order);
    }
}
