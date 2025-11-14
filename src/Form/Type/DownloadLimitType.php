<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DownloadLimitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('downloadLimit', IntegerType::class, [
                'label' => 'sylius_digital_product.ui.uploaded_file.download_limit_per_customer',
                'help' => 'sylius_digital_product.ui.uploaded_file.download_limit_per_customer_help',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            if (null === $data && isset($options['default_limit'])) {
                $data['downloadLimit'] = $options['default_limit'];
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('default_limit')
            ->setAllowedTypes('default_limit', ['int', 'null'])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_digital_product_download_limit';
    }
}
