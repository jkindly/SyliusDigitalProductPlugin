<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\ResponseGenerator;

use SyliusDigitalProductPlugin\Dto\DigitalFileDtoInterface;
use SyliusDigitalProductPlugin\Dto\ExternalUrlDigitalFileDto;
use SyliusDigitalProductPlugin\Entity\OrderItemFileInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final readonly class ExternalUrlFileResponseGenerator implements FileResponseGeneratorInterface
{
    public function __construct(
        private string $externalUrlType,
    ) {
    }

    public function generate(OrderItemFileInterface $file, DigitalFileDtoInterface $dto): Response
    {
        Assert::isInstanceOf($dto, ExternalUrlDigitalFileDto::class);

        $url = $dto->getUrl();
        if (null === $url) {
            throw new NotFoundHttpException('External URL not found in configuration.');
        }

        if (!filter_var($url, \FILTER_VALIDATE_URL)) {
            throw new NotFoundHttpException('Invalid URL in configuration.');
        }

        return new RedirectResponse($url);
    }

    public function supports(string $fileType): bool
    {
        return $this->externalUrlType === $fileType;
    }
}
