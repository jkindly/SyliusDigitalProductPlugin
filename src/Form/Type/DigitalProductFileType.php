<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use SyliusDigitalProductPlugin\Provider\DigitalFileProviderRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

final class DigitalProductFileType extends AbstractType
{
    public function __construct(private readonly DigitalFileProviderRegistryInterface $registry)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach ($this->registry->getAll() as $type => $provider) {
            $choices[$provider->getLabel()] = $type;
        }

        $builder->add('type', ChoiceType::class, [
            'label' => 'sylius_digital_product.ui.type',
            'choices' => $choices,
            'placeholder' => 'sylius_digital_product.ui.choose_type',
            'required' => true,
            // autosubmit po zmianie (patrz pkt 2):
            'attr' => ['onchange' => 'this.form.requestSubmit()'],
        ]);

        // inicjalizacja przy edycji / kiedy dane są już ustawione
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData() ?? [];
            $type = \is_array($data) ? ($data['type'] ?? null) : ($data->getType() ?? null);
            $this->addPayloadSubform($event->getForm(), $type);
        });

        // kluczowe: dołóż sub-formę na podstawie wybranej opcji w submitcie
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData() ?? [];
            $type = $data['type'] ?? null;
            $this->addPayloadSubform($event->getForm(), $type);
        });
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_file';
    }

    private function addPayloadSubform(FormInterface $form, ?string $type): void
    {
        if (!$type) {
            if ($form->has('payload')) {
                $form->remove('payload');
            }
            return;
        }

        $provider = $this->registry->get($type);
        $form->add('payload', $provider->getFormType(), [
            'label' => false,
            // ewentualnie: 'validation_groups' => ['Default', 'digital_file_'.$type],
        ]);
    }
}
