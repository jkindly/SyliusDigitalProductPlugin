<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\ResponseGenerator;

use SyliusDigitalProductPlugin\Dto\FileDtoInterface;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final readonly class UploadedFileResponseGenerator implements FileResponseGeneratorInterface
{
    public function __construct(
        private string $uploadedFileType,
        private string $uploadDirectory,
    ) {
    }

    public function generate(DigitalProductOrderItemFileInterface $file, FileDtoInterface $dto): Response
    {
        Assert::isInstanceOf($dto, UploadedFileDto::class);

        $path = $dto->getPath();
        if (null === $path) {
            throw new NotFoundHttpException('File path not found in configuration.');
        }

        $absolutePath = sprintf('%s/%s', rtrim($this->uploadDirectory, '/'), ltrim($path, '/'));

        $realUploadPath = realpath($this->uploadDirectory);
        $realFilePath = realpath($absolutePath);

        if (false === $realFilePath || false === $realUploadPath || !str_starts_with($realFilePath, $realUploadPath)) {
            throw new NotFoundHttpException('File not found or path validation failed.');
        }

        if (!file_exists($realFilePath)) {
            throw new NotFoundHttpException(sprintf('File does not exist: %s', $path));
        }

        $response = new BinaryFileResponse($realFilePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->sanitizeFilename($file->getName(), $dto),
        );

        return $response;
    }

    public function supports(string $fileType): bool
    {
        return $this->uploadedFileType === $fileType;
    }

    private function sanitizeFilename(?string $name, UploadedFileDto $dto): string
    {
        $name = !empty($name) ? $name : $dto->getPath();

        $extension = $dto->getExtension();
        $baseName = pathinfo($name ?? 'file', \PATHINFO_FILENAME);

        return $baseName . ($extension ? '.' . $extension : '');
    }
}
