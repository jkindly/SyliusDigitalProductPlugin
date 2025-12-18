<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SyliusDigitalProductExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $uploadedFile = $config['uploaded_file'] ?? [];

        $container->setParameter('sylius_digital_product_plugin.uploaded_file.delete_from_storage_on_remove', $uploadedFile['delete_from_storage_on_remove']);

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);

        $validatorConfig = [
            'mapping' => [
                'paths' => [
                    __DIR__ . '/../../config/validator',
                ],
            ],
        ];

        $container->prependExtensionConfig('framework', ['validation' => $validatorConfig]);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'SyliusDigitalProductPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@SyliusDigitalProductPlugin/src/Migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }

    private function getCurrentConfiguration(ContainerBuilder $container): array
    {
        /** @var ConfigurationInterface $configuration */
        $configuration = $this->getConfiguration([], $container);
        $configs = $container->getExtensionConfig($this->getAlias());

        return $this->processConfiguration($configuration, $configs);
    }
}
