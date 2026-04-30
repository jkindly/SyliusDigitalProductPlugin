<?php

declare(strict_types=1);

namespace Tests\Jkindly\SyliusDigitalProductPlugin\Unit\EventListener;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use Jkindly\SyliusDigitalProductPlugin\EventListener\RemoveUploadedFileListener;
use Jkindly\SyliusDigitalProductPlugin\Uploader\DigitalProductFileUploaderInterface;

final class RemoveUploadedFileListenerTest extends TestCase
{
    private MockObject&DigitalProductFileUploaderInterface $uploader;
    private RemoveUploadedFileListener $listener;

    protected function setUp(): void
    {
        $this->uploader = $this->createMock(DigitalProductFileUploaderInterface::class);
        $this->listener = new RemoveUploadedFileListener($this->uploader, true);
    }

    public function testPreRemoveCallsUploaderRemove(): void
    {
        $file = $this->createMock(DigitalProductFileInterface::class);

        $this->uploader->expects($this->once())
            ->method('remove')
            ->with($file);

        $this->listener->preRemove($file);
    }

    public function testPreRemovePassesCorrectFileInstance(): void
    {
        $file = $this->createMock(DigitalProductFileInterface::class);

        $this->uploader->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($file));

        $this->listener->preRemove($file);
    }
}
