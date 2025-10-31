<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Validator;

use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Webmozart\Assert\Assert;

final readonly class UploadedDigitalFileDtoValidator extends BaseDigitalFileDtoValidator
{
}
