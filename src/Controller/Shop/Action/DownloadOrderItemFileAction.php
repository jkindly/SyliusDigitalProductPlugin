<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Controller\Shop\Action;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\User\Model\UserInterface;
use SyliusDigitalProductPlugin\Entity\OrderItemFileInterface;
use SyliusDigitalProductPlugin\Repository\OrderItemFileRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final readonly class DownloadOrderItemFileAction
{
    public function __construct(
        private OrderItemFileRepositoryInterface $orderItemFileRepository,
        private Security $security,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(string $uuid): Response
    {
        $user = $this->security->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        /** @var OrderItemFileInterface|null $file */
        $file = $this->orderItemFileRepository->findOneByUuidAndUser($uuid, $user);
        if (null === $file) {
            throw new NotFoundHttpException('Order item file not found.');
        }

        $downloadLimit = $file->getDownloadLimit();
        if (null !== $downloadLimit && $file->getDownloadCount() >= $downloadLimit) {
            throw new AccessDeniedException('Download limit exceeded for this file.');
        }

        $file->incrementDownloadCount();

        $this->entityManager->flush();
    }
}
