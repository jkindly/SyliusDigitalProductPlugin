<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\EventListener\ProductVariantListener;
use SyliusDigitalProductPlugin\Handler\FileHandlerInterface;
use SyliusDigitalProductPlugin\Provider\UploadedFileProvider;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use SyliusDigitalProductPlugin\Factory\ChunkedUploadedFileFactoryInterface;
use SyliusDigitalProductPlugin\Uploader\ChunkedUploadHandlerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ProductVariantListenerTest extends TestCase
{
    private MockObject&FileHandlerInterface $fileHandler;
    private MockObject&FileConfigurationSerializerInterface $serializer;
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&ChunkedUploadedFileFactoryInterface $chunkedUploadedFileFactory;
    private MockObject&ChunkedUploadHandlerInterface $chunkedUploadHandler;
    private ProductVariantListener $listener;

    protected function setUp(): void
    {
        $this->fileHandler = $this->createMock(FileHandlerInterface::class);
        $this->serializer = $this->createMock(FileConfigurationSerializerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->chunkedUploadedFileFactory = $this->createMock(ChunkedUploadedFileFactoryInterface::class);
        $this->chunkedUploadHandler = $this->createMock(ChunkedUploadHandlerInterface::class);

        $this->listener = new ProductVariantListener(
            $this->fileHandler,
            $this->serializer,
            $this->entityManager,
            UploadedFileProvider::TYPE,
            $this->chunkedUploadedFileFactory,
            $this->chunkedUploadHandler
        );
    }

    public function testHandleProductVariantUpdateProcessesUploadedFiles(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $configuration = ['uploadedFile' => $uploadedFile];
        $newConfiguration = ['path' => '/path/to/file'];

        $file = $this->createMock(DigitalProductFileInterface::class);
        $file->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file->method('getConfiguration')->willReturn($configuration);
        $file->expects($this->once())->method('setConfiguration')->with($newConfiguration);

        $files = new ArrayCollection([$file]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getFiles')->willReturn($files);

        $dto = new UploadedFileDto();
        $this->serializer->expects($this->once())->method('getDto')->with($configuration)->willReturn($dto);
        $this->serializer->expects($this->once())->method('getConfiguration')->with($dto)->willReturn($newConfiguration);

        $this->fileHandler->expects($this->once())->method('handle')->with($dto);

        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateSkipsNonUploadedFileTypes(): void
    {
        $file = $this->createMock(DigitalProductFileInterface::class);
        $file->method('getType')->willReturn('external_url');

        $files = new ArrayCollection([$file]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getFiles')->willReturn($files);

        $this->fileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateSkipsFilesWithoutUploadedFile(): void
    {
        $configuration = ['path' => '/existing/path'];

        $file = $this->createMock(DigitalProductFileInterface::class);
        $file->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file->method('getConfiguration')->willReturn($configuration);

        $files = new ArrayCollection([$file]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getFiles')->willReturn($files);

        $this->fileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateProcessesMultipleFiles(): void
    {
        $uploadedFile1 = $this->createMock(UploadedFile::class);
        $uploadedFile2 = $this->createMock(UploadedFile::class);

        $file1 = $this->createMock(DigitalProductFileInterface::class);
        $file1->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file1->method('getConfiguration')->willReturn(['uploadedFile' => $uploadedFile1]);
        $file1->expects($this->once())->method('setConfiguration');

        $file2 = $this->createMock(DigitalProductFileInterface::class);
        $file2->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file2->method('getConfiguration')->willReturn(['uploadedFile' => $uploadedFile2]);
        $file2->expects($this->once())->method('setConfiguration');

        $files = new ArrayCollection([$file1, $file2]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getFiles')->willReturn($files);

        $dto = new UploadedFileDto();
        $this->serializer->method('getDto')->willReturn($dto);
        $this->serializer->method('getConfiguration')->willReturn([]);

        $this->fileHandler->expects($this->exactly(2))->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateSetsUploadedFileOnDto(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $configuration = ['uploadedFile' => $uploadedFile];

        $file = $this->createMock(DigitalProductFileInterface::class);
        $file->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file->method('getConfiguration')->willReturn($configuration);

        $files = new ArrayCollection([$file]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getFiles')->willReturn($files);

        $dto = new UploadedFileDto();
        $this->serializer->method('getDto')->willReturn($dto);
        $this->serializer->method('getConfiguration')->willReturn([]);

        $this->fileHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function ($arg) use ($uploadedFile) {
                return $arg instanceof UploadedFileDto && $arg->getUploadedFile() === $uploadedFile;
            }));

        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleSimpleProductUpdateProcessesFiles(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $configuration = ['uploadedFile' => $uploadedFile];

        $file = $this->createMock(DigitalProductFileInterface::class);
        $file->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file->method('getConfiguration')->willReturn($configuration);
        $file->expects($this->once())->method('setConfiguration');

        $files = new ArrayCollection([$file]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getFiles')->willReturn($files);

        $variants = new ArrayCollection([$variant]);

        $product = $this->createMock(ProductInterface::class);
        $product->method('isSimple')->willReturn(true);
        $product->method('getVariants')->willReturn($variants);

        $dto = new UploadedFileDto();
        $this->serializer->method('getDto')->willReturn($dto);
        $this->serializer->method('getConfiguration')->willReturn([]);

        $this->fileHandler->expects($this->once())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($product);
        $this->listener->handleSimpleProductUpdate($event);
    }

    public function testHandleSimpleProductUpdateSkipsNonSimpleProducts(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $product->method('isSimple')->willReturn(false);

        $this->fileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->never())->method('flush');

        $event = new GenericEvent($product);
        $this->listener->handleSimpleProductUpdate($event);
    }

    public function testHandleSimpleProductUpdateWithNoFiles(): void
    {
        $files = new ArrayCollection([]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getFiles')->willReturn($files);

        $variants = new ArrayCollection([$variant]);

        $product = $this->createMock(ProductInterface::class);
        $product->method('isSimple')->willReturn(true);
        $product->method('getVariants')->willReturn($variants);

        $this->fileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($product);
        $this->listener->handleSimpleProductUpdate($event);
    }

    public function testHandleProductVariantUpdateWithEmptyFiles(): void
    {
        $files = new ArrayCollection([]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getFiles')->willReturn($files);

        $this->fileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateWithMixedFileTypes(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);

        $file1 = $this->createMock(DigitalProductFileInterface::class);
        $file1->method('getType')->willReturn('external_url');

        $file2 = $this->createMock(DigitalProductFileInterface::class);
        $file2->method('getType')->willReturn(UploadedFileProvider::TYPE);
        $file2->method('getConfiguration')->willReturn(['uploadedFile' => $uploadedFile]);
        $file2->expects($this->once())->method('setConfiguration');

        $file3 = $this->createMock(DigitalProductFileInterface::class);
        $file3->method('getType')->willReturn('external_url');

        $files = new ArrayCollection([$file1, $file2, $file3]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getFiles')->willReturn($files);

        $dto = new UploadedFileDto();
        $this->serializer->method('getDto')->willReturn($dto);
        $this->serializer->method('getConfiguration')->willReturn([]);

        $this->fileHandler->expects($this->once())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }
}
