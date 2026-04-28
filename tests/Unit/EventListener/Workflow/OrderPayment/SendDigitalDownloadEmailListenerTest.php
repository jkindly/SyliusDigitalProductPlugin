<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\EventListener\Workflow\OrderPayment;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use SyliusDigitalProductPlugin\CommandDispatcher\ResendDigitalDownloadEmailDispatcherInterface;
use SyliusDigitalProductPlugin\EventListener\Workflow\OrderPayment\SendDigitalDownloadEmailListener;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;
use Webmozart\Assert\InvalidArgumentException;

final class SendDigitalDownloadEmailListenerTest extends TestCase
{
    private MockObject&ResendDigitalDownloadEmailDispatcherInterface $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(ResendDigitalDownloadEmailDispatcherInterface::class);
    }

    private function createListener(): SendDigitalDownloadEmailListener
    {
        return new SendDigitalDownloadEmailListener($this->dispatcher);
    }

    private function createEvent(object $subject): CompletedEvent
    {
        $workflow = $this->createMock(WorkflowInterface::class);
        $transition = new Transition('pay', 'awaiting_payment', 'paid');

        return new CompletedEvent($subject, new Marking(), $transition, $workflow);
    }

    public function testDispatchesEmailForOrder(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($order);

        ($this->createListener())($this->createEvent($order));
    }

    public function testThrowsWhenSubjectIsNotAnOrder(): void
    {
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->expectException(InvalidArgumentException::class);

        ($this->createListener())($this->createEvent(new \stdClass()));
    }
}
