<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Dto;

class ExternalUrlDigitalFileDto implements DigitalFileDtoInterface
{
    protected ?string $url = null;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
}
