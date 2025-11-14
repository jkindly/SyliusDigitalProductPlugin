<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Twig\Component\ProductVariant;

use Sylius\Bundle\UiBundle\Twig\Component\LiveCollectionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Sylius\Bundle\AdminBundle\Twig\Component\ProductVariant\FormComponent as BaseFormComponent;

#[AsLiveComponent]
class FormComponent extends BaseFormComponent
{
    use LiveCollectionTrait;

    protected function getDataModelValue(): string
    {
        return 'norender|*';
    }
}
