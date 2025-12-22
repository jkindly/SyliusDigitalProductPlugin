<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\ResponseGenerator;

use League\Flysystem\FilesystemOperator;
use SyliusDigitalProductPlugin\Dto\FileDtoInterface;
use SyliusDigitalProductPlugin\Dto\UploadedFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final readonly class UploadedFileResponseGenerator implements FileResponseGeneratorInterface
{
    public function __construct(
        private FilesystemOperator $localStorage,
        private string $uploadedFileType,
    ) {
    }

    public function generate(DigitalProductOrderItemFileInterface $file, FileDtoInterface $dto): Response
    {
        Assert::isInstanceOf($dto, UploadedFileDto::class);

        $path = $dto->getPath();
        if (null === $path) {
            throw new NotFoundHttpException('File path not found in configuration.');
        }

        if (false === $this->localStorage->fileExists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        $response = new StreamedResponse(function () use ($path) {
            $stream = $this->localStorage->readStream($path);
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        });

        $response->headers->set(
            'Content-Type',
            $dto->getMimeType() ?? 'application/octet-stream',
        );

        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $this->sanitizeFilename($file->getName(), $dto),
            ),
        );

        if (null !== $size = $dto->getSize()) {
            $response->headers->set('Content-Length', (string) $size);
        }

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
