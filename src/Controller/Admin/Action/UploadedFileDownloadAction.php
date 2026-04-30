<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Controller\Admin\Action;

use Sylius\Component\Core\Model\AdminUserInterface;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductFile;
use Jkindly\SyliusDigitalProductPlugin\Repository\DigitalProductFileRepositoryInterface;
use Jkindly\SyliusDigitalProductPlugin\ResponseGenerator\FileResponseGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class UploadedFileDownloadAction
{
    public function __construct(
        private DigitalProductFileRepositoryInterface $fileRepository,
        private Security $security,
        private FileResponseGeneratorInterface $uploadedFileResponseGenerator,
    ) {
    }

    public function __invoke(int $id, Request $request): Response
    {
        $file = $this->fileRepository->find($id);
        if (!$file instanceof DigitalProductFile) {
            throw new NotFoundHttpException('Digital file not found');
        }

        /** @var AdminUserInterface $user */
        $user = $this->security->getUser();
        if (!$user->hasRole(AdminUserInterface::DEFAULT_ADMIN_ROLE)) {
            throw new AccessDeniedHttpException('Only admins can download this file.');
        }

        return $this->uploadedFileResponseGenerator->generate($file);
    }
}
