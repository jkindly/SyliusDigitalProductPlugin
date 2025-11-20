<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\ResponseGenerator;

use SyliusDigitalProductPlugin\Dto\DigitalFileDtoInterface;
use SyliusDigitalProductPlugin\Entity\OrderItemFileInterface;
use Symfony\Component\HttpFoundation\Response;

interface FileResponseGeneratorInterface
{
    public function generate(OrderItemFileInterface $file, DigitalFileDtoInterface $dto): Response;

    public function supports(string $fileType): bool;
}
