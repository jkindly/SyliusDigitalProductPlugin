<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Controller\Admin\Action;

use League\Flysystem\FilesystemOperator;
use SyliusDigitalProductPlugin\Uploader\ChunkedUploadHandlerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;

final readonly class ChunkedUploadAction
{
    public function __construct(
        private ChunkedUploadHandlerInterface $chunkedUploadHandler,
        private FilesystemOperator $chunksStorage,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No file provided'], Response::HTTP_BAD_REQUEST);
        }

        $chunkIndex = (int) $request->request->get('chunkIndex', 0);
        $totalChunks = (int) $request->request->get('totalChunks', 1);
        $fileId = $request->request->get('fileId');
        $originalFilename = $request->request->get('filename');

        if (!is_string($fileId) || '' === $fileId) {
            return new JsonResponse(['error' => 'Invalid fileId'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_string($originalFilename) || '' === $originalFilename) {
            return new JsonResponse(['error' => 'Invalid filename'], Response::HTTP_BAD_REQUEST);
        }

        if (preg_match('#(\.\./|/\.\.)#', $originalFilename)) {
            return new JsonResponse(['error' => 'Invalid filename: path traversal detected'], Response::HTTP_BAD_REQUEST);
        }

        $basename = basename($originalFilename);
        if ($originalFilename !== $basename) {
            return new JsonResponse(['error' => 'Invalid filename: must be a filename without path'], Response::HTTP_BAD_REQUEST);
        }

        $this->chunkedUploadHandler->saveChunk($fileId, $chunkIndex, $file, $originalFilename);

        $isLastChunk = ($totalChunks - 1) === $chunkIndex;

        if ($isLastChunk) {
            $mergedPath = $this->chunkedUploadHandler->mergeChunks($fileId, $totalChunks, $originalFilename);

            $extension = pathinfo($originalFilename, \PATHINFO_EXTENSION);
            $mimeTypes = new MimeTypes();
            $mimeType = $extension ? ($mimeTypes->getMimeTypes($extension)[0]
                ?? 'application/octet-stream') : 'application/octet-stream';
            $size = $this->chunksStorage->fileSize($mergedPath);

            return new JsonResponse([
                'success' => true,
                'completed' => true,
                'fileId' => $fileId,
                'originalFilename' => $originalFilename,
                'extension' => $extension,
                'mimeType' => $mimeType,
                'size' => $size,
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'completed' => false,
            'chunkIndex' => $chunkIndex,
        ]);
    }
}
