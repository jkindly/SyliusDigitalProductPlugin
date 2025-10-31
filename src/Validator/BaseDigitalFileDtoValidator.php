<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Validator;

use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Webmozart\Assert\Assert;

abstract readonly class BaseDigitalFileDtoValidator implements DigitalFileDtoValidatorInterface
{
    public function __construct(
        private ClassMetadataFactoryInterface $classMetadataFactory,
        private DigitalFileConfigurationSerializerInterface $serializer,
    ) {
    }

    public function validate(mixed $data, ExecutionContextInterface $context, mixed $payload): void
    {
        if (!is_array($data)) {
            return;
        }

        $form = $context->getObject();
        Assert::isInstanceOf($form, FormInterface::class);

        $config = $form->getConfig();

        $dtoClass = $config->getDataClass();
        Assert::notNull($dtoClass);

        $dto = $this->serializer->getDto($data);

        $groups = $config->getOption('validation_groups');
        Assert::nullOrIsArray($groups);

        foreach ($this->getIgnoredAttributes($dtoClass) as $attribute) {
            $setter = 'set' . ucfirst($attribute);
            if (method_exists($dto, $setter) && method_exists($form, 'get')) {
                $dto->$setter($form->get($attribute)->getData());
            }
        }

        $violations = $context->getValidator()->validate(value: $dto, groups: $groups);
        foreach ($violations as $violation) {
            $context
                ->buildViolation((string) $violation->getMessage())
                ->atPath($violation->getPropertyPath())
                ->addViolation()
            ;
        }
    }

    private function getIgnoredAttributes(string $dtoClass): array
    {
        $metadata = $this->classMetadataFactory->getMetadataFor($dtoClass);
        $ignoredAttributes = [];

        foreach ($metadata->getAttributesMetadata() as $attributeMetadata) {
            if ($attributeMetadata->isIgnored()) {
                $ignoredAttributes[] = $attributeMetadata->getName();
            }
        }

        return $ignoredAttributes;
    }
}
