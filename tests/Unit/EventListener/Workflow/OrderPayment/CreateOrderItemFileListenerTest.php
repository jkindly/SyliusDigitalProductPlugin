<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\EventListener\Workflow\OrderPayment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileOwnedSettingsInterface;
use SyliusDigitalProductPlugin\EventListener\Workflow\OrderPayment\CreateOrderItemFileListener;
use SyliusDigitalProductPlugin\Factory\OrderItemFileFactoryInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

final class CreateOrderItemFileListenerTest extends TestCase
{
    private MockObject&OrderItemFileFactoryInterface $orderItemFileFactory;
    private MockObject&EntityManagerInterface $entityManager;
    private CreateOrderItemFileListener $listener;

    protected function setUp(): void
    {
        $this->orderItemFileFactory = $this->createMock(OrderItemFileFactoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new CreateOrderItemFileListener(
            $this->orderItemFileFactory,
            $this->entityManager
        );
    }

    public function testInvokeCreatesOrderItemFilesForDigitalProducts(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $channel = $this->createMock(DigitalProductChannelInterface::class);
        $channelSettings = $this->createMock(DigitalProductChannelSettingsInterface::class);

        $orderItem = $this->createMock(OrderItemInterface::class);
        $variant = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );
        $file = $this->createMock(DigitalProductFileInterface::class);
        $orderItemFile = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $workflow = $this->createMock(WorkflowInterface::class);
        $marking = new Marking();
        $transition = new Transition('pay', 'new', 'paid');
        $event = new CompletedEvent($order, $marking, $transition, $workflow);

        $order->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $order->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$orderItem]));

        $orderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(true);


        $channel->expects($this->once())
            ->method('getDigitalProductFileChannelSettings')
            ->willReturn($channelSettings);

        $variant->expects($this->once())
            ->method('getFiles')
            ->willReturn(new ArrayCollection([$file]));

        $file->expects($this->once())
            ->method('getName')
            ->willReturn('Test File');

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn(['path' => '/test/path']);

        $fileSettings = $this->createMock(DigitalProductFileOwnedSettingsInterface::class);
        $fileSettings->method('getDownloadLimit')->willReturn(10);
        $file->method('getSettings')->willReturn($fileSettings);

        $this->orderItemFileFactory->expects($this->once())
            ->method('createWithData')
            ->with($orderItem, 'Test File', 'uploaded_file', 10, ['path' => '/test/path'])
            ->willReturn($orderItemFile);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($orderItemFile);

        ($this->listener)($event);
    }

    public function testInvokeSkipsNonDigitalProducts(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $channel = $this->createMock(DigitalProductChannelInterface::class);

        $orderItem = $this->createMock(OrderItemInterface::class);
        $variant = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );

        $workflow = $this->createMock(WorkflowInterface::class);
        $marking = new Marking();
        $transition = new Transition('pay', 'new', 'paid');
        $event = new CompletedEvent($order, $marking, $transition, $workflow);

        $order->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $order->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$orderItem]));

        $orderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(false);

        $this->orderItemFileFactory->expects($this->never())
            ->method('createWithData');

        $this->entityManager->expects($this->never())
            ->method('persist');

        ($this->listener)($event);
    }

    public function testInvokeUsesChannelDownloadLimitWhenVariantLimitIsNull(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $channel = $this->createMock(DigitalProductChannelInterface::class);
        $channelSettings = $this->createMock(DigitalProductChannelSettingsInterface::class);

        $orderItem = $this->createMock(OrderItemInterface::class);
        $variant = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );
        $file = $this->createMock(DigitalProductFileInterface::class);
        $orderItemFile = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $workflow = $this->createMock(WorkflowInterface::class);
        $marking = new Marking();
        $transition = new Transition('pay', 'new', 'paid');
        $event = new CompletedEvent($order, $marking, $transition, $workflow);

        $order->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $order->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$orderItem]));

        $orderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(true);


        $channel->expects($this->once())
            ->method('getDigitalProductFileChannelSettings')
            ->willReturn($channelSettings);

        $variant->expects($this->once())
            ->method('getFiles')
            ->willReturn(new ArrayCollection([$file]));

        $file->expects($this->once())
            ->method('getName')
            ->willReturn('Test File');

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn(['path' => '/test/path']);

        $file->method('getSettings')->willReturn(null);

        $channelSettings->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(5);

        $this->orderItemFileFactory->expects($this->once())
            ->method('createWithData')
            ->with($orderItem, 'Test File', 'uploaded_file', 5, ['path' => '/test/path'])
            ->willReturn($orderItemFile);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($orderItemFile);

        ($this->listener)($event);
    }

    public function testInvokeUsesNullDownloadLimitWhenBothSettingsAreNull(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $channel = $this->createMock(DigitalProductChannelInterface::class);

        $orderItem = $this->createMock(OrderItemInterface::class);
        $variant = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );
        $file = $this->createMock(DigitalProductFileInterface::class);
        $orderItemFile = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $workflow = $this->createMock(WorkflowInterface::class);
        $marking = new Marking();
        $transition = new Transition('pay', 'new', 'paid');
        $event = new CompletedEvent($order, $marking, $transition, $workflow);

        $order->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $order->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$orderItem]));

        $orderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(true);


        $channel->expects($this->once())
            ->method('getDigitalProductFileChannelSettings')
            ->willReturn(null);

        $variant->expects($this->once())
            ->method('getFiles')
            ->willReturn(new ArrayCollection([$file]));

        $file->expects($this->once())
            ->method('getName')
            ->willReturn('Test File');

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn(['path' => '/test/path']);

        $file->method('getSettings')->willReturn(null);

        $this->orderItemFileFactory->expects($this->once())
            ->method('createWithData')
            ->with($orderItem, 'Test File', 'uploaded_file', null, ['path' => '/test/path'])
            ->willReturn($orderItemFile);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($orderItemFile);

        ($this->listener)($event);
    }

    public function testInvokeCreatesMultipleOrderItemFilesForMultipleFiles(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $channel = $this->createMock(DigitalProductChannelInterface::class);
        $channelSettings = $this->createMock(DigitalProductChannelSettingsInterface::class);

        $orderItem = $this->createMock(OrderItemInterface::class);
        $variant = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );

        $file1 = $this->createMock(DigitalProductFileInterface::class);
        $file2 = $this->createMock(DigitalProductFileInterface::class);
        $orderItemFile1 = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $orderItemFile2 = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $workflow = $this->createMock(WorkflowInterface::class);
        $marking = new Marking();
        $transition = new Transition('pay', 'new', 'paid');
        $event = new CompletedEvent($order, $marking, $transition, $workflow);

        $order->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $order->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$orderItem]));

        $orderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(true);


        $channel->expects($this->once())
            ->method('getDigitalProductFileChannelSettings')
            ->willReturn($channelSettings);

        $variant->expects($this->once())
            ->method('getFiles')
            ->willReturn(new ArrayCollection([$file1, $file2]));

        $file1->expects($this->once())
            ->method('getName')
            ->willReturn('File 1');

        $file1->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file1->expects($this->once())
            ->method('getConfiguration')
            ->willReturn(['path' => '/test/path1']);

        $file2->expects($this->once())
            ->method('getName')
            ->willReturn('File 2');

        $file2->expects($this->once())
            ->method('getType')
            ->willReturn('external_url');

        $file2->expects($this->once())
            ->method('getConfiguration')
            ->willReturn(['url' => 'https://example.com/file']);

        $fileSettings1 = $this->createMock(DigitalProductFileOwnedSettingsInterface::class);
        $fileSettings1->method('getDownloadLimit')->willReturn(3);
        $file1->method('getSettings')->willReturn($fileSettings1);

        $fileSettings2 = $this->createMock(DigitalProductFileOwnedSettingsInterface::class);
        $fileSettings2->method('getDownloadLimit')->willReturn(3);
        $file2->method('getSettings')->willReturn($fileSettings2);

        $this->orderItemFileFactory->expects($this->exactly(2))
            ->method('createWithData')
            ->willReturnCallback(function ($item, $name, $type, $limit, $config) use ($orderItem, $orderItemFile1, $orderItemFile2) {
                if ('File 1' === $name) {
                    $this->assertSame($orderItem, $item);
                    $this->assertSame('uploaded_file', $type);
                    $this->assertSame(3, $limit);
                    $this->assertSame(['path' => '/test/path1'], $config);
                    return $orderItemFile1;
                }
                if ('File 2' === $name) {
                    $this->assertSame($orderItem, $item);
                    $this->assertSame('external_url', $type);
                    $this->assertSame(3, $limit);
                    $this->assertSame(['url' => 'https://example.com/file'], $config);
                    return $orderItemFile2;
                }
                $this->fail('Unexpected file name');
            });

        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($file) use ($orderItemFile1, $orderItemFile2) {
                $this->assertTrue($file === $orderItemFile1 || $file === $orderItemFile2);
            });

        ($this->listener)($event);
    }

    public function testInvokeHandlesMultipleOrderItems(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $channel = $this->createMock(DigitalProductChannelInterface::class);
        $channelSettings = $this->createMock(DigitalProductChannelSettingsInterface::class);

        $orderItem1 = $this->createMock(OrderItemInterface::class);
        $orderItem2 = $this->createMock(OrderItemInterface::class);
        $variant1 = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );
        $variant2 = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );

        $file1 = $this->createMock(DigitalProductFileInterface::class);
        $file2 = $this->createMock(DigitalProductFileInterface::class);
        $orderItemFile1 = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $orderItemFile2 = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $workflow = $this->createMock(WorkflowInterface::class);
        $marking = new Marking();
        $transition = new Transition('pay', 'new', 'paid');
        $event = new CompletedEvent($order, $marking, $transition, $workflow);

        $order->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $order->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$orderItem1, $orderItem2]));

        $orderItem1->expects($this->once())
            ->method('getVariant')
            ->willReturn($variant1);

        $orderItem2->expects($this->once())
            ->method('getVariant')
            ->willReturn($variant2);

        $variant1->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(true);

        $variant2->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(true);



        $channel->expects($this->exactly(2))
            ->method('getDigitalProductFileChannelSettings')
            ->willReturn($channelSettings);

        $variant1->expects($this->once())
            ->method('getFiles')
            ->willReturn(new ArrayCollection([$file1]));

        $variant2->expects($this->once())
            ->method('getFiles')
            ->willReturn(new ArrayCollection([$file2]));

        $file1->expects($this->once())
            ->method('getName')
            ->willReturn('Item 1 File');

        $file1->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file1->expects($this->once())
            ->method('getConfiguration')
            ->willReturn(['path' => '/item1/path']);

        $file2->expects($this->once())
            ->method('getName')
            ->willReturn('Item 2 File');

        $file2->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file2->expects($this->once())
            ->method('getConfiguration')
            ->willReturn(['path' => '/item2/path']);

        $fileSettings1 = $this->createMock(DigitalProductFileOwnedSettingsInterface::class);
        $fileSettings1->method('getDownloadLimit')->willReturn(5);
        $file1->method('getSettings')->willReturn($fileSettings1);

        $fileSettings2 = $this->createMock(DigitalProductFileOwnedSettingsInterface::class);
        $fileSettings2->method('getDownloadLimit')->willReturn(10);
        $file2->method('getSettings')->willReturn($fileSettings2);

        $this->orderItemFileFactory->expects($this->exactly(2))
            ->method('createWithData')
            ->willReturnCallback(function ($item, $name, $type, $limit, $config) use ($orderItem1, $orderItem2, $orderItemFile1, $orderItemFile2) {
                if ($item === $orderItem1) {
                    $this->assertSame('Item 1 File', $name);
                    $this->assertSame(5, $limit);
                    return $orderItemFile1;
                }
                if ($item === $orderItem2) {
                    $this->assertSame('Item 2 File', $name);
                    $this->assertSame(10, $limit);
                    return $orderItemFile2;
                }
                $this->fail('Unexpected order item');
            });

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        ($this->listener)($event);
    }

    public function testInvokeHandlesMixedDigitalAndNonDigitalProducts(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $channel = $this->createMock(DigitalProductChannelInterface::class);
        $channelSettings = $this->createMock(DigitalProductChannelSettingsInterface::class);

        $digitalOrderItem = $this->createMock(OrderItemInterface::class);
        $nonDigitalOrderItem = $this->createMock(OrderItemInterface::class);
        $digitalVariant = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );
        $nonDigitalVariant = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );

        $file = $this->createMock(DigitalProductFileInterface::class);
        $orderItemFile = $this->createMock(DigitalProductOrderItemFileInterface::class);

        $workflow = $this->createMock(WorkflowInterface::class);
        $marking = new Marking();
        $transition = new Transition('pay', 'new', 'paid');
        $event = new CompletedEvent($order, $marking, $transition, $workflow);

        $order->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $order->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$digitalOrderItem, $nonDigitalOrderItem]));

        $digitalOrderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($digitalVariant);

        $nonDigitalOrderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($nonDigitalVariant);

        $digitalVariant->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(true);

        $nonDigitalVariant->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(false);


        $channel->expects($this->once())
            ->method('getDigitalProductFileChannelSettings')
            ->willReturn($channelSettings);

        $digitalVariant->expects($this->once())
            ->method('getFiles')
            ->willReturn(new ArrayCollection([$file]));

        $file->expects($this->once())
            ->method('getName')
            ->willReturn('Digital File');

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn(['path' => '/digital/path']);

        $fileSettings = $this->createMock(DigitalProductFileOwnedSettingsInterface::class);
        $fileSettings->method('getDownloadLimit')->willReturn(7);
        $file->method('getSettings')->willReturn($fileSettings);

        $this->orderItemFileFactory->expects($this->once())
            ->method('createWithData')
            ->with($digitalOrderItem, 'Digital File', 'uploaded_file', 7, ['path' => '/digital/path'])
            ->willReturn($orderItemFile);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($orderItemFile);

        ($this->listener)($event);
    }

    public function testInvokeHandlesEmptyFilesCollection(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $channel = $this->createMock(DigitalProductChannelInterface::class);
        $channelSettings = $this->createMock(DigitalProductChannelSettingsInterface::class);

        $orderItem = $this->createMock(OrderItemInterface::class);
        $variant = $this->getMockForAbstractClass(
            DigitalProductVariantInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['hasAnyFile', 'getFiles']
        );

        $workflow = $this->createMock(WorkflowInterface::class);
        $marking = new Marking();
        $transition = new Transition('pay', 'new', 'paid');
        $event = new CompletedEvent($order, $marking, $transition, $workflow);

        $order->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $order->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$orderItem]));

        $orderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($variant);

        $variant->expects($this->once())
            ->method('hasAnyFile')
            ->willReturn(true);


        $channel->expects($this->once())
            ->method('getDigitalProductFileChannelSettings')
            ->willReturn($channelSettings);

        $variant->expects($this->once())
            ->method('getFiles')
            ->willReturn(new ArrayCollection([]));

        $this->orderItemFileFactory->expects($this->never())
            ->method('createWithData');

        $this->entityManager->expects($this->never())
            ->method('persist');

        ($this->listener)($event);
    }

    public function testInvokeHandlesOrderWithNoItems(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $channel = $this->createMock(DigitalProductChannelInterface::class);

        $workflow = $this->createMock(WorkflowInterface::class);
        $marking = new Marking();
        $transition = new Transition('pay', 'new', 'paid');
        $event = new CompletedEvent($order, $marking, $transition, $workflow);

        $order->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $order->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([]));

        $this->orderItemFileFactory->expects($this->never())
            ->method('createWithData');

        $this->entityManager->expects($this->never())
            ->method('persist');

        ($this->listener)($event);
    }
}
