<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;
use SyliusDigitalProductPlugin\Provider\FileProviderRegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class DigitalProductFileType extends AbstractResourceType
{
    private array $fileTypes = [];

    public function __construct(
        private readonly FileProviderRegistryInterface $registry,
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
            ->add('settings', DigitalProductFileSettingsType::class, [
                'required' => false,
            ])
        ;

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $this->addFileTypeToForm($event);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $this->addFileTypeToForm($event);
                $this->applySettings($event);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefined('channel_settings')
            ->setAllowedTypes('channel_settings', [DigitalProductChannelSettingsInterface::class, 'null'])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_file';
    }

    private function addFileTypeToForm(FormEvent $event): void
    {
        $data = $event->getData();
        if (null === $data) {
            return;
        }

        if (!is_array($data) && !$data instanceof DigitalProductFileInterface) {
            return;
        }

        $form = $event->getForm();

        $dataType = $data instanceof DigitalProductFileInterface ? $data->getType() : $data['type'];
        $formType = $this->fileTypes[$dataType];

        $form->add('configuration', $formType);
    }

    private function applySettings(FormEvent $event): void
    {
        /** @var DigitalProductChannelSettingsInterface|null $settings */
        $settings = $event->getForm()->getConfig()->getOption('channel_settings');
        if (null === $settings) {
            return;
        }

        Assert::isInstanceOf($settings, DigitalProductChannelSettingsInterface::class);

        $data = $event->getData();
        Assert::isArray($data);

        if (!isset($data['settings'])) {
            $data['settings']['downloadLimit'] = $settings->getDownloadLimit();
            $data['settings']['daysAvailable'] = $settings->getDaysAvailable();
            $data['settings']['hiddenQuantity'] = $settings->isHiddenQuantity();
        }

        $event->setData($data);
    }
}
