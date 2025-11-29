<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\ResponseGenerator;

use SyliusDigitalProductPlugin\Dto\FileDtoInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use Symfony\Component\HttpFoundation\Response;

interface FileResponseGeneratorInterface
{
    public function generate(DigitalProductOrderItemFileInterface $file, FileDtoInterface $dto): Response;

    public function supports(string $fileType): bool;
}
