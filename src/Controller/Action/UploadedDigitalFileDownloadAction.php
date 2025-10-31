<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Controller\Action;

use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalFile;
use SyliusDigitalProductPlugin\Repository\DigitalFileRepositoryInterface;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class UploadedDigitalFileDownloadAction
{
    public function __construct(
        private readonly DigitalFileRepositoryInterface $digitalFileRepository,
        private readonly DigitalFileConfigurationSerializerInterface $uploadedFileSerializer,
        private readonly string $uploadedFilesPath,
    ) {
    }

    public function __invoke(int $id): Response
    {
        $file = $this->digitalFileRepository->find($id);
        Assert::isInstanceOf($file, DigitalFile::class);

        /** @var UploadedDigitalFileDto $dto */
        $dto = $this->uploadedFileSerializer->getDto($file->getConfiguration());

        $response = new BinaryFileResponse(sprintf('%s/%s', rtrim($this->uploadedFilesPath, '/'), $dto->getPath()));

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            sprintf('%s.%s', ($dto->getName() ?? $dto->getOriginalFilename() ?? 'file'), $dto->getExtension()),
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
