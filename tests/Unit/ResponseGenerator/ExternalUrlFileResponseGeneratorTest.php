<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\ResponseGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\ExternalUrlFileDto;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFileInterface;
use SyliusDigitalProductPlugin\Provider\ExternalUrlFileProvider;
use SyliusDigitalProductPlugin\ResponseGenerator\ExternalUrlFileResponseGenerator;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerInterface;
use SyliusDigitalProductPlugin\Serializer\FileConfigurationSerializerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ExternalUrlFileResponseGeneratorTest extends TestCase
{
    private ExternalUrlFileResponseGenerator $generator;
    private FileConfigurationSerializerInterface&MockObject $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(FileConfigurationSerializerInterface::class);
        $serializerRegistry = new FileConfigurationSerializerRegistry([
            ExternalUrlFileProvider::TYPE => $this->serializer,
        ]);

        $this->generator = new ExternalUrlFileResponseGenerator(
            $serializerRegistry,
            ExternalUrlFileProvider::TYPE
        );
    }

    private function createFileWithDto(ExternalUrlFileDto $dto): DigitalProductOrderItemFileInterface&MockObject
    {
        $configuration = ['url' => $dto->getUrl()];
        $file = $this->createMock(DigitalProductOrderItemFileInterface::class);
        $file->method('getType')->willReturn(ExternalUrlFileProvider::TYPE);
        $file->method('getConfiguration')->willReturn($configuration);
        $this->serializer->method('getDto')->with($configuration)->willReturn($dto);
        return $file;
    }

    public function testGenerateReturnsRedirectResponse(): void
    {
        $dto = new ExternalUrlFileDto();
        $dto->setUrl('https://example.com/file.pdf');

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testGenerateRedirectsToCorrectUrl(): void
    {
        $url = 'https://example.com/downloads/file.pdf';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesHttpsUrls(): void
    {
        $url = 'https://secure.example.com/file.pdf';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesHttpUrls(): void
    {
        $url = 'http://example.com/file.pdf';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithQueryParameters(): void
    {
        $url = 'https://example.com/file.pdf?token=abc123&expires=3600';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithFragments(): void
    {
        $url = 'https://example.com/file.pdf#section1';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateThrowsNotFoundExceptionWhenUrlIsNull(): void
    {
        $dto = new ExternalUrlFileDto();
        $dto->setUrl(null);

        $file = $this->createFileWithDto($dto);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('External URL not found in configuration.');

        $this->generator->generate($file);
    }

    public function testGenerateThrowsNotFoundExceptionWhenUrlIsInvalid(): void
    {
        $dto = new ExternalUrlFileDto();
        $dto->setUrl('not-a-valid-url');

        $file = $this->createFileWithDto($dto);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid URL in configuration.');

        $this->generator->generate($file);
    }

    public function testGenerateThrowsNotFoundExceptionForEmptyUrl(): void
    {
        $dto = new ExternalUrlFileDto();
        $dto->setUrl('');

        $file = $this->createFileWithDto($dto);

        $this->expectException(NotFoundHttpException::class);

        $this->generator->generate($file);
    }

    public function testGenerateThrowsNotFoundExceptionForInvalidProtocol(): void
    {
        $dto = new ExternalUrlFileDto();
        $dto->setUrl('javascript:alert(1)');

        $file = $this->createFileWithDto($dto);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid URL in configuration.');

        $this->generator->generate($file);
    }

    public function testGenerateThrowsNotFoundExceptionForRelativeUrl(): void
    {
        $dto = new ExternalUrlFileDto();
        $dto->setUrl('/relative/path/file.pdf');

        $file = $this->createFileWithDto($dto);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid URL in configuration.');

        $this->generator->generate($file);
    }

    public function testGenerateHandlesFtpUrls(): void
    {
        $url = 'ftp://ftp.example.com/files/document.pdf';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithEncodedInternationalCharacters(): void
    {
        $url = 'https://example.com/' . rawurlencode('файл') . '.pdf';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithPort(): void
    {
        $url = 'https://example.com:8080/file.pdf';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesUrlsWithAuthentication(): void
    {
        $url = 'https://user:password@example.com/file.pdf';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testGenerateHandlesLongUrls(): void
    {
        $url = 'https://example.com/very/long/path/to/file/' . str_repeat('a', 200) . '.pdf';

        $dto = new ExternalUrlFileDto();
        $dto->setUrl($url);

        $file = $this->createFileWithDto($dto);

        $response = $this->generator->generate($file);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function testSupportsReturnsTrueForExternalUrlType(): void
    {
        $this->assertTrue($this->generator->supports(ExternalUrlFileProvider::TYPE));
    }

    public function testSupportsReturnsFalseForOtherFileTypes(): void
    {
        $this->assertFalse($this->generator->supports('uploaded_file'));
        $this->assertFalse($this->generator->supports('s3_file'));
        $this->assertFalse($this->generator->supports('random_type'));
    }
}
