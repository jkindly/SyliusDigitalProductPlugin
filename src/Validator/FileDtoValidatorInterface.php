<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

interface FileDtoValidatorInterface
{
    public function validate(mixed $data, ExecutionContextInterface $context, mixed $payload): void;
}
