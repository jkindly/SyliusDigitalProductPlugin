<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\ResponseGenerator;

use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\ExternalUrlDigitalFileDto;
use SyliusDigitalProductPlugin\Entity\OrderItemFileInterface;
use SyliusDigitalProductPlugin\Provider\ExternalUrlDigitalFileProvider;
use SyliusDigitalProductPlugin\ResponseGenerator\ExternalUrlFileResponseGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ExternalUrlFileResponseGeneratorTest extends TestCase
{
    private ExternalUrlFileResponseGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new ExternalUrlFileResponseGenerator(
            ExternalUrlDigitalFileProvider::TYPE
        );
    }

    public function testGenerateReturnsRedirectResponse(): void
    {
        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl('https://example.com/file.pdf');

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testGenerateRedirectsToCorrectUrl(): void
    {
        $url = 'https://example.com/downloads/file.pdf';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesHttpsUrls(): void
    {
        $url = 'https://secure.example.com/file.pdf';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesHttpUrls(): void
    {
        $url = 'http://example.com/file.pdf';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithQueryParameters(): void
    {
        $url = 'https://example.com/file.pdf?token=abc123&expires=3600';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithFragments(): void
    {
        $url = 'https://example.com/file.pdf#section1';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateThrowsNotFoundExceptionWhenUrlIsNull(): void
    {
        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl(null);

        $file = $this->createMock(OrderItemFileInterface::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('External URL not found in configuration.');

        $this->generator->generate($file, $dto);
    }

    public function testGenerateThrowsNotFoundExceptionWhenUrlIsInvalid(): void
    {
        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl('not-a-valid-url');

        $file = $this->createMock(OrderItemFileInterface::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid URL in configuration.');

        $this->generator->generate($file, $dto);
    }

    public function testGenerateThrowsNotFoundExceptionForEmptyUrl(): void
    {
        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl('');

        $file = $this->createMock(OrderItemFileInterface::class);

        $this->expectException(NotFoundHttpException::class);

        $this->generator->generate($file, $dto);
    }

    public function testGenerateThrowsNotFoundExceptionForInvalidProtocol(): void
    {
        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl('javascript:alert(1)');

        $file = $this->createMock(OrderItemFileInterface::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid URL in configuration.');

        $this->generator->generate($file, $dto);
    }

    public function testGenerateThrowsNotFoundExceptionForRelativeUrl(): void
    {
        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl('/relative/path/file.pdf');

        $file = $this->createMock(OrderItemFileInterface::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid URL in configuration.');

        $this->generator->generate($file, $dto);
    }

    public function testGenerateHandlesFtpUrls(): void
    {
        $url = 'ftp://ftp.example.com/files/document.pdf';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithEncodedInternationalCharacters(): void
    {
        $url = 'https://example.com/' . rawurlencode('файл') . '.pdf';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithPort(): void
    {
        $url = 'https://example.com:8080/file.pdf';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithAuthentication(): void
    {
        $url = 'https://user:password@example.com/file.pdf';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesLongUrls(): void
    {
        $url = 'https://example.com/very/long/path/to/file/' . str_repeat('a', 200) . '.pdf';

        $dto = new ExternalUrlDigitalFileDto();
        $dto->setUrl($url);

        $file = $this->createMock(OrderItemFileInterface::class);

        $response = $this->generator->generate($file, $dto);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testSupportsReturnsTrueForExternalUrlType(): void
    {
        $this->assertTrue($this->generator->supports(ExternalUrlDigitalFileProvider::TYPE));
    }

    public function testSupportsReturnsFalseForOtherFileTypes(): void
    {
        $this->assertFalse($this->generator->supports('uploaded_file'));
        $this->assertFalse($this->generator->supports('s3_file'));
        $this->assertFalse($this->generator->supports('random_type'));
    }
}
