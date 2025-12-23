<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\ResponseGenerator;

use SyliusDigitalProductPlugin\Dto\ExternalUrlFileDto;
use SyliusDigitalProductPlugin\Dto\FileDtoInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileBaseInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final readonly class ExternalUrlFileResponseGenerator implements FileResponseGeneratorInterface
{
    public function __construct(
        private FileConfigurationSerializerRegistry $serializerRegistry,
        private string $externalUrlType,
    ) {
    }

    public function generate(DigitalProductFileBaseInterface $file): Response
    {
        $fileType = $file->getType();
        Assert::notNull($fileType);

        $serializer = $this->serializerRegistry->get($fileType);
        $dto = $serializer->getDto($file->getConfiguration());

        Assert::isInstanceOf($dto, ExternalUrlFileDto::class);

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
