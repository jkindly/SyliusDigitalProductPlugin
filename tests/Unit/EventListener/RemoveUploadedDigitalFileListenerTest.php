<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\EventListener;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\EventListener\RemoveUploadedDigitalFileListener;
use SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final class RemoveUploadedDigitalFileListenerTest extends TestCase
{
    private MockObject&DigitalProductFileUploaderInterface $uploader;
    private RemoveUploadedDigitalFileListener $listener;

    protected function setUp(): void
    {
        $this->uploader = $this->createMock(DigitalProductFileUploaderInterface::class);
        $this->listener = new RemoveUploadedDigitalFileListener($this->uploader);
    }

    public function testPreRemoveCallsUploaderRemove(): void
    {
        $file = $this->createMock(DigitalFileInterface::class);

        $this->uploader->expects($this->once())
            ->method('remove')
            ->with($file);

        $this->listener->preRemove($file);
    }

    public function testPreRemovePassesCorrectFileInstance(): void
    {
        $file = $this->createMock(DigitalFileInterface::class);

        $this->uploader->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($file));

        $this->listener->preRemove($file);
    }
}
