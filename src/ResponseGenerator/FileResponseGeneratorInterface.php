<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\ResponseGenerator;

use SyliusDigitalProductPlugin\Dto\FileDtoInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileBaseInterface;
use Symfony\Component\HttpFoundation\Response;

interface FileResponseGeneratorInterface
{
    public function generate(DigitalProductFileBaseInterface $file): Response;

    public function supports(string $fileType): bool;
}
