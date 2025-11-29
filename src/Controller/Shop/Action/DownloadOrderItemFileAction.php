<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Controller\Shop\Action;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\User\Model\UserInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use SyliusDigitalProductPlugin\Repository\DigitalProductOrderItemFileRepositoryInterface;
use SyliusDigitalProductPlugin\ResponseGenerator\FileResponseGeneratorRegistry;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final readonly class DownloadOrderItemFileAction
{
    public function __construct(
        private DigitalProductOrderItemFileRepositoryInterface $orderItemFileRepository,
        private Security $security,
        private EntityManagerInterface $entityManager,
        private FileResponseGeneratorRegistry $responseGeneratorRegistry,
        private FileConfigurationSerializerRegistry $serializerRegistry,
    ) {
    }

    public function __invoke(string $uuid): Response
    {
        $user = $this->security->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        /** @var DigitalProductOrderItemFileInterface|null $file */
        $file = $this->orderItemFileRepository->findOneByUuidAndUser($uuid, $user);
        if (null === $file) {
            throw new NotFoundHttpException('Order item file not found.');
        }

        $downloadLimit = $file->getDownloadLimit();
        if (null !== $downloadLimit && $file->getDownloadCount() >= $downloadLimit) {
            throw new AccessDeniedException('Download limit exceeded for this file.');
        }

        if (false === $file->isAvailable()) {
            throw new AccessDeniedException('This file is no longer available for download.');
        }

        $fileType = $file->getType();
        Assert::notNull($fileType, 'File type must not be null.');

        $serializer = $this->serializerRegistry->get($fileType);
        $configurationDto = $serializer->getDto($file->getConfiguration());

        $file->incrementDownloadCount();
        $this->entityManager->flush();

        return $this->responseGeneratorRegistry->get($fileType)->generate($file, $configurationDto);
    }
}
