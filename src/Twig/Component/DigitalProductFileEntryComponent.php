<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Twig\Component;

use Sylius\Bundle\UiBundle\Twig\Component\TemplatePropTrait;
use SyliusDigitalProductPlugin\Form\Type\DigitalProductFileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DigitalProductFileEntryComponent extends AbstractController
{
    use DefaultActionTrait;
    use TemplatePropTrait;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly RequestStack $requestStack,
    ) {}

    /** Aktualnie wybrany typ (bindowany z selecta) */
    #[LiveProp(writable: true)]
    public ?string $type = null;

    /** Dane entry (np. z kolekcji) – prosty array jest OK */
    #[LiveProp(writable: true)]
    public array $data = [];

    public function getForm(): FormInterface
    {
        // wstrzykujemy aktualny 'type' do danych, żeby PRE_SET_DATA zadziałało
        $data = $this->data;
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }

        // ważne: nazwa formularza nie kolidowała z innymi entry
        return $this->formFactory->createNamed(
            'digital_file_entry_' . spl_object_id($this),
            DigitalProductFileType::class,
            $data,
            ['csrf_protection' => false] // entry i tak finalnie zapisze parent form
        );
    }

    /** Wywoływane przy zmianie selecta (bez pełnego submitu) */
    public function updateType(#[LiveArg] string $type): void
    {
        $this->type = $type;
    }
}
