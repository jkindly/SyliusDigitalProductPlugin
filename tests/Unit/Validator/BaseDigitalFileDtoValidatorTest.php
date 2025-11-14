<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Dto\UploadedDigitalFileDto;
use SyliusDigitalProductPlugin\Serializer\DigitalFileConfigurationSerializerInterface;
use SyliusDigitalProductPlugin\Validator\UploadedDigitalFileDtoValidator;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class BaseDigitalFileDtoValidatorTest extends TestCase
{
    private MockObject&ClassMetadataFactoryInterface $classMetadataFactory;
    private MockObject&DigitalFileConfigurationSerializerInterface $serializer;
    private MockObject&ExecutionContextInterface $context;
    private MockObject&ValidatorInterface $validator;
    private UploadedDigitalFileDtoValidator $validatorService;

    protected function setUp(): void
    {
        $this->classMetadataFactory = $this->createMock(ClassMetadataFactoryInterface::class);
        $this->serializer = $this->createMock(DigitalFileConfigurationSerializerInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->validatorService = new UploadedDigitalFileDtoValidator(
            $this->classMetadataFactory,
            $this->serializer
        );
    }

    public function testValidateSkipsNonArrayData(): void
    {
        $this->context->expects($this->never())->method('getObject');
        $this->serializer->expects($this->never())->method('getDto');

        $this->validatorService->validate('string', $this->context, null);
    }

    public function testValidateSkipsNullData(): void
    {
        $this->context->expects($this->never())->method('getObject');
        $this->serializer->expects($this->never())->method('getDto');

        $this->validatorService->validate(null, $this->context, null);
    }

    public function testValidateProcessesArrayData(): void
    {
        $data = ['path' => '/test/path'];
        $dto = new UploadedDigitalFileDto();

        $form = $this->createMock(FormInterface::class);
        $config = $this->createMock(FormConfigInterface::class);
        $classMetadata = $this->createMock(ClassMetadataInterface::class);

        $this->context->expects($this->once())->method('getObject')->willReturn($form);
        $form->expects($this->once())->method('getConfig')->willReturn($config);
        $config->expects($this->once())->method('getDataClass')->willReturn(UploadedDigitalFileDto::class);
        $config->expects($this->once())->method('getOption')->with('validation_groups')->willReturn(null);

        $this->serializer->expects($this->once())->method('getDto')->with($data)->willReturn($dto);

        $this->classMetadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(UploadedDigitalFileDto::class)
            ->willReturn($classMetadata);

        $classMetadata->expects($this->once())->method('getAttributesMetadata')->willReturn([]);

        $this->context->expects($this->once())->method('getValidator')->willReturn($this->validator);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with(
                $this->identicalTo($dto),
                $this->anything(),
                $this->identicalTo(null)
            )
            ->willReturn(new ConstraintViolationList());

        $this->validatorService->validate($data, $this->context, null);
    }

    public function testValidateAddsViolationsToContext(): void
    {
        $data = ['path' => '/test/path'];
        $dto = new UploadedDigitalFileDto();

        $form = $this->createMock(FormInterface::class);
        $config = $this->createMock(FormConfigInterface::class);
        $classMetadata = $this->createMock(ClassMetadataInterface::class);

        $this->context->expects($this->once())->method('getObject')->willReturn($form);
        $form->expects($this->once())->method('getConfig')->willReturn($config);
        $config->expects($this->once())->method('getDataClass')->willReturn(UploadedDigitalFileDto::class);
        $config->expects($this->once())->method('getOption')->with('validation_groups')->willReturn(null);

        $this->serializer->expects($this->once())->method('getDto')->with($data)->willReturn($dto);

        $this->classMetadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(UploadedDigitalFileDto::class)
            ->willReturn($classMetadata);

        $classMetadata->expects($this->once())->method('getAttributesMetadata')->willReturn([]);

        $violation = new ConstraintViolation(
            'Error message',
            null,
            [],
            null,
            'path',
            null
        );

        $violations = new ConstraintViolationList([$violation]);

        $this->context->expects($this->once())->method('getValidator')->willReturn($this->validator);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with(
                $this->identicalTo($dto),
                $this->anything(),
                $this->identicalTo(null)
            )
            ->willReturn($violations);

        $violationBuilder = $this->createMock(\Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('Error message')
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->once())
            ->method('atPath')
            ->with('path')
            ->willReturnSelf();

        $violationBuilder->expects($this->once())->method('addViolation');

        $this->validatorService->validate($data, $this->context, null);
    }

    public function testValidateWithValidationGroups(): void
    {
        $data = ['path' => '/test/path'];
        $dto = new UploadedDigitalFileDto();
        $groups = ['Default', 'custom_group'];

        $form = $this->createMock(FormInterface::class);
        $config = $this->createMock(FormConfigInterface::class);
        $classMetadata = $this->createMock(ClassMetadataInterface::class);

        $this->context->expects($this->once())->method('getObject')->willReturn($form);
        $form->expects($this->once())->method('getConfig')->willReturn($config);
        $config->expects($this->once())->method('getDataClass')->willReturn(UploadedDigitalFileDto::class);
        $config->expects($this->once())->method('getOption')->with('validation_groups')->willReturn($groups);

        $this->serializer->expects($this->once())->method('getDto')->with($data)->willReturn($dto);

        $this->classMetadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(UploadedDigitalFileDto::class)
            ->willReturn($classMetadata);

        $classMetadata->expects($this->once())->method('getAttributesMetadata')->willReturn([]);

        $this->context->expects($this->once())->method('getValidator')->willReturn($this->validator);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with(
                $this->identicalTo($dto),
                $this->anything(),
                $this->identicalTo($groups)
            )
            ->willReturn(new ConstraintViolationList());

        $this->validatorService->validate($data, $this->context, null);
    }

    public function testValidateHandlesIgnoredAttributes(): void
    {
        $data = ['path' => '/test/path'];
        $dto = new UploadedDigitalFileDto();

        $form = $this->createMock(FormInterface::class);
        $config = $this->createMock(FormConfigInterface::class);
        $classMetadata = $this->createMock(ClassMetadataInterface::class);
        $attributeMetadata = $this->createMock(AttributeMetadataInterface::class);
        $childForm = $this->createMock(FormInterface::class);

        $this->context->expects($this->once())->method('getObject')->willReturn($form);
        $form->expects($this->once())->method('getConfig')->willReturn($config);
        $form->expects($this->once())->method('get')->with('uploadedFile')->willReturn($childForm);
        $childForm->expects($this->once())->method('getData')->willReturn(null);

        $config->expects($this->once())->method('getDataClass')->willReturn(UploadedDigitalFileDto::class);
        $config->expects($this->once())->method('getOption')->with('validation_groups')->willReturn(null);

        $this->serializer->expects($this->once())->method('getDto')->with($data)->willReturn($dto);

        $this->classMetadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(UploadedDigitalFileDto::class)
            ->willReturn($classMetadata);

        $attributeMetadata->expects($this->once())->method('isIgnored')->willReturn(true);
        $attributeMetadata->expects($this->once())->method('getName')->willReturn('uploadedFile');

        $classMetadata->expects($this->once())
            ->method('getAttributesMetadata')
            ->willReturn([$attributeMetadata]);

        $this->context->expects($this->once())->method('getValidator')->willReturn($this->validator);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with(
                $this->identicalTo($dto),
                $this->anything(),
                $this->identicalTo(null)
            )
            ->willReturn(new ConstraintViolationList());

        $this->validatorService->validate($data, $this->context, null);
    }

    public function testValidateAddsMultipleViolations(): void
    {
        $data = ['path' => '/test/path'];
        $dto = new UploadedDigitalFileDto();

        $form = $this->createMock(FormInterface::class);
        $config = $this->createMock(FormConfigInterface::class);
        $classMetadata = $this->createMock(ClassMetadataInterface::class);

        $this->context->expects($this->once())->method('getObject')->willReturn($form);
        $form->expects($this->once())->method('getConfig')->willReturn($config);
        $config->expects($this->once())->method('getDataClass')->willReturn(UploadedDigitalFileDto::class);
        $config->expects($this->once())->method('getOption')->with('validation_groups')->willReturn(null);

        $this->serializer->expects($this->once())->method('getDto')->with($data)->willReturn($dto);

        $this->classMetadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(UploadedDigitalFileDto::class)
            ->willReturn($classMetadata);

        $classMetadata->expects($this->once())->method('getAttributesMetadata')->willReturn([]);

        $violation1 = new ConstraintViolation('Error 1', null, [], null, 'path', null);
        $violation2 = new ConstraintViolation('Error 2', null, [], null, 'name', null);

        $violations = new ConstraintViolationList([$violation1, $violation2]);

        $this->context->expects($this->once())->method('getValidator')->willReturn($this->validator);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with(
                $this->identicalTo($dto),
                $this->anything(),
                $this->identicalTo(null)
            )
            ->willReturn($violations);

        $violationBuilder = $this->createMock(\Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->exactly(2))
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->exactly(2))
            ->method('atPath')
            ->willReturnSelf();

        $violationBuilder->expects($this->exactly(2))->method('addViolation');

        $this->validatorService->validate($data, $this->context, null);
    }
}
