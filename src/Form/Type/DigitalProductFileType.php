<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Sylius\Component\Promotion\Model\CatalogPromotionScopeInterface;
use SyliusDigitalProductPlugin\Provider\DigitalFileProviderInterface;
use SyliusDigitalProductPlugin\Provider\DigitalFileProviderRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

final class DigitalProductFileType extends AbstractType
{
    private array $fileTypes = [];

    public function __construct(private readonly DigitalFileProviderRegistryInterface $registry)
    {
        foreach ($this->registry->getAll() as $key => $provider) {
            $this->fileTypes[$key] = $provider->getFormType();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', HiddenType::class);

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $this->addFileTypeToForm($event);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $this->addFileTypeToForm($event);
            })
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_file';
    }

    private function addFileTypeToForm(FormEvent $event): void
    {
        $data = $event->getData();
        if ($data === null) {
            return;
        }
        $dataType = $data instanceof DigitalFileProviderInterface ? $data->getType() : $data['type'];
        $scopeConfigurationType = $this->fileTypes[$dataType];

        $form = $event->getForm();
        $form->add('configuration', $scopeConfigurationType);
    }
}
