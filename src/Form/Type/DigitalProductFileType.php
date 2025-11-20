<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use SyliusDigitalProductPlugin\Entity\DigitalFileInterface;
use SyliusDigitalProductPlugin\Provider\DigitalFileProviderRegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

final class DigitalProductFileType extends AbstractResourceType
{
    private array $fileTypes = [];

    public function __construct(
        private readonly DigitalFileProviderRegistryInterface $registry,
        protected string $dataClass,
        protected array $validationGroups = [],
    ) {
        parent::__construct($dataClass, $validationGroups);

        foreach ($this->registry->getAll() as $key => $provider) {
            $this->fileTypes[$key] = $provider->getFormType();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', HiddenType::class)
            ->add('name', TextType::class, [
                'label' => 'sylius_digital_product.ui.name',
            ])
        ;

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

        if (!is_array($data) && !$data instanceof DigitalFileInterface) {
            return;
        }

        $dataType = $data instanceof DigitalFileInterface ? $data->getType() : $data['type'];
        $formType = $this->fileTypes[$dataType];
        $form = $event->getForm();
        $form->add('configuration', $formType);
    }
}
