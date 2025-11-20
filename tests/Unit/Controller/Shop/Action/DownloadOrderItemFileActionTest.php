<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Controller\Shop\Action;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\User\Model\UserInterface;
use SyliusDigitalProductPlugin\Controller\Shop\Action\DownloadOrderItemFileAction;
use SyliusDigitalProductPlugin\Dto\DigitalFileDtoInterface;
use SyliusDigitalProductPlugin\Dto\ExternalUrlDigitalFileDto;
use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Entity\OrderItemFileInterface;
use SyliusDigitalProductPlugin\Repository\OrderItemFileRepositoryInterface;
use SyliusDigitalProductPlugin\ResponseGenerator\FileResponseGeneratorInterface;
use SyliusDigitalProductPlugin\ResponseGenerator\FileResponseGeneratorRegistry;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DownloadOrderItemFileActionTest extends TestCase
{
    private MockObject&OrderItemFileRepositoryInterface $orderItemFileRepository;
    private MockObject&Security $security;
    private MockObject&EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->orderItemFileRepository = $this->createMock(OrderItemFileRepositoryInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    private function createAction(
        array $generators = [],
        array $serializers = []
    ): DownloadOrderItemFileAction {
        $responseGeneratorRegistry = new FileResponseGeneratorRegistry($generators);
        $serializerRegistry = new DigitalFileConfigurationSerializerRegistry($serializers);

        return new DownloadOrderItemFileAction(
            $this->orderItemFileRepository,
            $this->security,
            $this->entityManager,
            $responseGeneratorRegistry,
            $serializerRegistry
        );
    }

    public function testInvokeReturnsResponseForSuccessfulDownload(): void
    {
        $uuid = 'test-uuid-123';
        $user = $this->createMock(UserInterface::class);
        $file = $this->createMock(OrderItemFileInterface::class);
        $serializer = $this->createMock(DigitalFileConfigurationSerializerInterface::class);
        $dto = $this->createMock(UploadedDigitalFileDto::class);
        $generator = $this->createMock(FileResponseGeneratorInterface::class);
        $response = $this->createMock(Response::class);

        $configuration = ['path' => '/test/path'];

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn($file);

        $file->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(10);

        $file->expects($this->once())
            ->method('getDownloadCount')
            ->willReturn(5);

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $file->expects($this->once())
            ->method('incrementDownloadCount');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $generator->expects($this->once())
            ->method('supports')
            ->with('uploaded_file')
            ->willReturn(true);

        $generator->expects($this->once())
            ->method('generate')
            ->with($file, $dto)
            ->willReturn($response);

        $action = $this->createAction([$generator], ['uploaded_file' => $serializer]);
        $result = $action($uuid);

        $this->assertSame($response, $result);
    }

    public function testInvokeThrowsNotFoundExceptionWhenFileNotFound(): void
    {
        $uuid = 'non-existent-uuid';
        $user = $this->createMock(UserInterface::class);

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Order item file not found.');

        $action = $this->createAction();
        $action($uuid);
    }

    public function testInvokeThrowsAccessDeniedExceptionWhenDownloadLimitExceeded(): void
    {
        $uuid = 'test-uuid-123';
        $user = $this->createMock(UserInterface::class);
        $file = $this->createMock(OrderItemFileInterface::class);

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn($file);

        $file->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(5);

        $file->expects($this->once())
            ->method('getDownloadCount')
            ->willReturn(5);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Download limit exceeded for this file.');

        $action = $this->createAction();
        $action($uuid);
    }

    public function testInvokeAllowsDownloadWhenDownloadLimitIsNull(): void
    {
        $uuid = 'test-uuid-123';
        $user = $this->createMock(UserInterface::class);
        $file = $this->createMock(OrderItemFileInterface::class);
        $serializer = $this->createMock(DigitalFileConfigurationSerializerInterface::class);
        $dto = $this->createMock(UploadedDigitalFileDto::class);
        $generator = $this->createMock(FileResponseGeneratorInterface::class);
        $response = $this->createMock(Response::class);

        $configuration = ['path' => '/test/path'];

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn($file);

        $file->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(null);

        $file->expects($this->never())
            ->method('getDownloadCount');

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $file->expects($this->once())
            ->method('incrementDownloadCount');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $generator->expects($this->once())
            ->method('supports')
            ->with('uploaded_file')
            ->willReturn(true);

        $generator->expects($this->once())
            ->method('generate')
            ->with($file, $dto)
            ->willReturn($response);

        $action = $this->createAction([$generator], ['uploaded_file' => $serializer]);
        $result = $action($uuid);

        $this->assertSame($response, $result);
    }

    public function testInvokeAllowsDownloadWhenCountIsJustBelowLimit(): void
    {
        $uuid = 'test-uuid-123';
        $user = $this->createMock(UserInterface::class);
        $file = $this->createMock(OrderItemFileInterface::class);
        $serializer = $this->createMock(DigitalFileConfigurationSerializerInterface::class);
        $dto = $this->createMock(UploadedDigitalFileDto::class);
        $generator = $this->createMock(FileResponseGeneratorInterface::class);
        $response = $this->createMock(Response::class);

        $configuration = ['path' => '/test/path'];

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn($file);

        $file->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(10);

        $file->expects($this->once())
            ->method('getDownloadCount')
            ->willReturn(9);

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $file->expects($this->once())
            ->method('incrementDownloadCount');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $generator->expects($this->once())
            ->method('supports')
            ->with('uploaded_file')
            ->willReturn(true);

        $generator->expects($this->once())
            ->method('generate')
            ->with($file, $dto)
            ->willReturn($response);

        $action = $this->createAction([$generator], ['uploaded_file' => $serializer]);
        $result = $action($uuid);

        $this->assertSame($response, $result);
    }

    public function testInvokeWorksWithExternalUrlFileType(): void
    {
        $uuid = 'test-uuid-123';
        $user = $this->createMock(UserInterface::class);
        $file = $this->createMock(OrderItemFileInterface::class);
        $serializer = $this->createMock(DigitalFileConfigurationSerializerInterface::class);
        $dto = $this->createMock(ExternalUrlDigitalFileDto::class);
        $generator = $this->createMock(FileResponseGeneratorInterface::class);
        $response = $this->createMock(Response::class);

        $configuration = ['url' => 'https://example.com/file.pdf'];

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn($file);

        $file->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(null);

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('external_url');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $file->expects($this->once())
            ->method('incrementDownloadCount');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $generator->expects($this->once())
            ->method('supports')
            ->with('external_url')
            ->willReturn(true);

        $generator->expects($this->once())
            ->method('generate')
            ->with($file, $dto)
            ->willReturn($response);

        $action = $this->createAction([$generator], ['external_url' => $serializer]);
        $result = $action($uuid);

        $this->assertSame($response, $result);
    }

    public function testInvokeIncrementsDownloadCountBeforeFlush(): void
    {
        $uuid = 'test-uuid-123';
        $user = $this->createMock(UserInterface::class);
        $file = $this->createMock(OrderItemFileInterface::class);
        $serializer = $this->createMock(DigitalFileConfigurationSerializerInterface::class);
        $dto = $this->createMock(UploadedDigitalFileDto::class);
        $generator = $this->createMock(FileResponseGeneratorInterface::class);
        $response = $this->createMock(Response::class);

        $configuration = ['path' => '/test/path'];
        $callOrder = [];

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn($file);

        $file->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(10);

        $file->expects($this->once())
            ->method('getDownloadCount')
            ->willReturn(0);

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $file->expects($this->once())
            ->method('incrementDownloadCount')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'increment';
            });

        $this->entityManager->expects($this->once())
            ->method('flush')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'flush';
            });

        $generator->expects($this->once())
            ->method('supports')
            ->with('uploaded_file')
            ->willReturn(true);

        $generator->expects($this->once())
            ->method('generate')
            ->with($file, $dto)
            ->willReturn($response);

        $action = $this->createAction([$generator], ['uploaded_file' => $serializer]);
        $action($uuid);

        $this->assertSame(['increment', 'flush'], $callOrder);
    }

    public function testInvokeThrowsAccessDeniedWhenCountExceedsLimit(): void
    {
        $uuid = 'test-uuid-123';
        $user = $this->createMock(UserInterface::class);
        $file = $this->createMock(OrderItemFileInterface::class);

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn($file);

        $file->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(5);

        $file->expects($this->once())
            ->method('getDownloadCount')
            ->willReturn(10);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Download limit exceeded for this file.');

        $action = $this->createAction();
        $action($uuid);
    }

    public function testInvokeAllowsFirstDownload(): void
    {
        $uuid = 'test-uuid-123';
        $user = $this->createMock(UserInterface::class);
        $file = $this->createMock(OrderItemFileInterface::class);
        $serializer = $this->createMock(DigitalFileConfigurationSerializerInterface::class);
        $dto = $this->createMock(UploadedDigitalFileDto::class);
        $generator = $this->createMock(FileResponseGeneratorInterface::class);
        $response = $this->createMock(Response::class);

        $configuration = ['path' => '/test/path'];

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn($file);

        $file->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(1);

        $file->expects($this->once())
            ->method('getDownloadCount')
            ->willReturn(0);

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('uploaded_file');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $file->expects($this->once())
            ->method('incrementDownloadCount');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $generator->expects($this->once())
            ->method('supports')
            ->with('uploaded_file')
            ->willReturn(true);

        $generator->expects($this->once())
            ->method('generate')
            ->with($file, $dto)
            ->willReturn($response);

        $action = $this->createAction([$generator], ['uploaded_file' => $serializer]);
        $result = $action($uuid);

        $this->assertSame($response, $result);
    }

    public function testInvokePassesCorrectDtoToGenerator(): void
    {
        $uuid = 'test-uuid-123';
        $user = $this->createMock(UserInterface::class);
        $file = $this->createMock(OrderItemFileInterface::class);
        $serializer = $this->createMock(DigitalFileConfigurationSerializerInterface::class);
        $dto = $this->createMock(DigitalFileDtoInterface::class);
        $generator = $this->createMock(FileResponseGeneratorInterface::class);
        $response = $this->createMock(Response::class);

        $configuration = ['custom' => 'data'];

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->orderItemFileRepository->expects($this->once())
            ->method('findOneByUuidAndUser')
            ->with($uuid, $user)
            ->willReturn($file);

        $file->expects($this->once())
            ->method('getDownloadLimit')
            ->willReturn(null);

        $file->expects($this->once())
            ->method('getType')
            ->willReturn('custom_type');

        $file->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($configuration);

        $serializer->expects($this->once())
            ->method('getDto')
            ->with($configuration)
            ->willReturn($dto);

        $file->expects($this->once())
            ->method('incrementDownloadCount');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $generator->expects($this->once())
            ->method('supports')
            ->with('custom_type')
            ->willReturn(true);

        $generator->expects($this->once())
            ->method('generate')
            ->with($file, $this->identicalTo($dto))
            ->willReturn($response);

        $action = $this->createAction([$generator], ['custom_type' => $serializer]);
        $result = $action($uuid);

        $this->assertSame($response, $result);
    }
}
