<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\EventListener\ProductVariantListener;
use SyliusDigitalProductPlugin\Handler\DigitalFileHandlerInterface;
use SyliusDigitalProductPlugin\Provider\UploadedDigitalFileProvider;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ProductVariantListenerTest extends TestCase
{
    private MockObject&DigitalFileHandlerInterface $digitalFileHandler;
    private MockObject&DigitalFileConfigurationSerializerInterface $serializer;
    private MockObject&EntityManagerInterface $entityManager;
    private ProductVariantListener $listener;

    protected function setUp(): void
    {
        $this->digitalFileHandler = $this->createMock(DigitalFileHandlerInterface::class);
        $this->serializer = $this->createMock(DigitalFileConfigurationSerializerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new ProductVariantListener(
            $this->digitalFileHandler,
            $this->serializer,
            $this->entityManager,
            UploadedDigitalFileProvider::TYPE
        );
    }

    public function testHandleProductVariantUpdateProcessesUploadedDigitalFiles(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $configuration = ['uploadedFile' => $uploadedFile];
        $newConfiguration = ['path' => '/path/to/file'];

        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile->method('getConfiguration')->willReturn($configuration);
        $digitalFile->expects($this->once())->method('setConfiguration')->with($newConfiguration);

        $digitalFiles = new ArrayCollection([$digitalFile]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getDigitalFiles')->willReturn($digitalFiles);

        $dto = new UploadedDigitalFileDto();
        $this->serializer->expects($this->once())->method('getDto')->with($configuration)->willReturn($dto);
        $this->serializer->expects($this->once())->method('getConfiguration')->with($dto)->willReturn($newConfiguration);

        $this->digitalFileHandler->expects($this->once())->method('handle')->with($dto);

        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateSkipsNonUploadedFileTypes(): void
    {
        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn('external_url');

        $digitalFiles = new ArrayCollection([$digitalFile]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getDigitalFiles')->willReturn($digitalFiles);

        $this->digitalFileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateSkipsFilesWithoutUploadedFile(): void
    {
        $configuration = ['path' => '/existing/path'];

        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile->method('getConfiguration')->willReturn($configuration);

        $digitalFiles = new ArrayCollection([$digitalFile]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getDigitalFiles')->willReturn($digitalFiles);

        $this->digitalFileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateProcessesMultipleFiles(): void
    {
        $uploadedFile1 = $this->createMock(UploadedFile::class);
        $uploadedFile2 = $this->createMock(UploadedFile::class);

        $digitalFile1 = $this->createMock(DigitalFileInterface::class);
        $digitalFile1->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile1->method('getConfiguration')->willReturn(['uploadedFile' => $uploadedFile1]);
        $digitalFile1->expects($this->once())->method('setConfiguration');

        $digitalFile2 = $this->createMock(DigitalFileInterface::class);
        $digitalFile2->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile2->method('getConfiguration')->willReturn(['uploadedFile' => $uploadedFile2]);
        $digitalFile2->expects($this->once())->method('setConfiguration');

        $digitalFiles = new ArrayCollection([$digitalFile1, $digitalFile2]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getDigitalFiles')->willReturn($digitalFiles);

        $dto = new UploadedDigitalFileDto();
        $this->serializer->method('getDto')->willReturn($dto);
        $this->serializer->method('getConfiguration')->willReturn([]);

        $this->digitalFileHandler->expects($this->exactly(2))->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateSetsUploadedFileOnDto(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $configuration = ['uploadedFile' => $uploadedFile];

        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile->method('getConfiguration')->willReturn($configuration);

        $digitalFiles = new ArrayCollection([$digitalFile]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getDigitalFiles')->willReturn($digitalFiles);

        $dto = new UploadedDigitalFileDto();
        $this->serializer->method('getDto')->willReturn($dto);
        $this->serializer->method('getConfiguration')->willReturn([]);

        $this->digitalFileHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function ($arg) use ($uploadedFile) {
                return $arg instanceof UploadedDigitalFileDto && $arg->getUploadedFile() === $uploadedFile;
            }));

        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleSimpleProductUpdateProcessesDigitalFiles(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $configuration = ['uploadedFile' => $uploadedFile];

        $digitalFile = $this->createMock(DigitalFileInterface::class);
        $digitalFile->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile->method('getConfiguration')->willReturn($configuration);
        $digitalFile->expects($this->once())->method('setConfiguration');

        $digitalFiles = new ArrayCollection([$digitalFile]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getDigitalFiles')->willReturn($digitalFiles);

        $variants = new ArrayCollection([$variant]);

        $product = $this->createMock(ProductInterface::class);
        $product->method('isSimple')->willReturn(true);
        $product->method('getVariants')->willReturn($variants);

        $dto = new UploadedDigitalFileDto();
        $this->serializer->method('getDto')->willReturn($dto);
        $this->serializer->method('getConfiguration')->willReturn([]);

        $this->digitalFileHandler->expects($this->once())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($product);
        $this->listener->handleSimpleProductUpdate($event);
    }

    public function testHandleSimpleProductUpdateSkipsNonSimpleProducts(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $product->method('isSimple')->willReturn(false);

        $this->digitalFileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->never())->method('flush');

        $event = new GenericEvent($product);
        $this->listener->handleSimpleProductUpdate($event);
    }

    public function testHandleSimpleProductUpdateWithNoDigitalFiles(): void
    {
        $digitalFiles = new ArrayCollection([]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getDigitalFiles')->willReturn($digitalFiles);

        $variants = new ArrayCollection([$variant]);

        $product = $this->createMock(ProductInterface::class);
        $product->method('isSimple')->willReturn(true);
        $product->method('getVariants')->willReturn($variants);

        $this->digitalFileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($product);
        $this->listener->handleSimpleProductUpdate($event);
    }

    public function testHandleProductVariantUpdateWithEmptyDigitalFiles(): void
    {
        $digitalFiles = new ArrayCollection([]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getDigitalFiles')->willReturn($digitalFiles);

        $this->digitalFileHandler->expects($this->never())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }

    public function testHandleProductVariantUpdateWithMixedFileTypes(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);

        $digitalFile1 = $this->createMock(DigitalFileInterface::class);
        $digitalFile1->method('getType')->willReturn('external_url');

        $digitalFile2 = $this->createMock(DigitalFileInterface::class);
        $digitalFile2->method('getType')->willReturn(UploadedDigitalFileProvider::TYPE);
        $digitalFile2->method('getConfiguration')->willReturn(['uploadedFile' => $uploadedFile]);
        $digitalFile2->expects($this->once())->method('setConfiguration');

        $digitalFile3 = $this->createMock(DigitalFileInterface::class);
        $digitalFile3->method('getType')->willReturn('external_url');

        $digitalFiles = new ArrayCollection([$digitalFile1, $digitalFile2, $digitalFile3]);

        $variant = $this->createMock(DigitalProductVariantInterface::class);
        $variant->method('getDigitalFiles')->willReturn($digitalFiles);

        $dto = new UploadedDigitalFileDto();
        $this->serializer->method('getDto')->willReturn($dto);
        $this->serializer->method('getConfiguration')->willReturn([]);

        $this->digitalFileHandler->expects($this->once())->method('handle');
        $this->entityManager->expects($this->once())->method('flush');

        $event = new GenericEvent($variant);
        $this->listener->handleProductVariantUpdate($event);
    }
}
