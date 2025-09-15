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
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_file';
    }
}
