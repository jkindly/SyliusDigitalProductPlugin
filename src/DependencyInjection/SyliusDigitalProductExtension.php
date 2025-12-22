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
    private const DEFAULT_PRODUCT_FILES_PATH = 'var/uploads/product_files';

    private const DEFAULT_ORDER_FILES_PATH = 'var/uploads/order_files';

    private const DEFAULT_CHUNKS_PATH = 'var/uploads/tmp/chunks';

    use PrependDoctrineMigrationsTrait;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);
        $this->prependParameters($container);

        $validatorConfig = [
            'mapping' => [
                'paths' => [
                    __DIR__ . '/../../config/validator',
                ],
            ],
        ];

        $container->prependExtensionConfig('framework', ['validation' => $validatorConfig]);
    }

    private function prependParameters(ContainerBuilder $container): void
    {
        $config = $this->getCurrentConfiguration($container);
        $uploadedFile = $config['uploaded_file'] ?? [];

        $container->setParameter('sylius_digital_product_plugin.uploaded_file.delete_from_storage_on_remove', $uploadedFile['delete_from_storage_on_remove']);
        $container->setParameter('sylius_digital_product_plugin.uploaded_file.chunk_size', $uploadedFile['chunk_size']);

        $kernelDir = $container->getParameter('kernel.project_dir');
        if (is_string($kernelDir)) {
            $container->setParameter(
                'sylius_digital_product_plugin.uploaded_file.product_files_path',
                $uploadedFile['product_files_path'] ?? sprintf('%s/%s', $kernelDir, self::DEFAULT_PRODUCT_FILES_PATH),
            );
            $container->setParameter(
                'sylius_digital_product_plugin.uploaded_file.order_files_path',
                $uploadedFile['order_files_path'] ?? sprintf('%s/%s', $kernelDir, self::DEFAULT_ORDER_FILES_PATH),
            );
            $container->setParameter(
                'sylius_digital_product_plugin.uploaded_file.chunks_path',
                $uploadedFile['chunks_path'] ?? sprintf('%s/%s', $kernelDir, self::DEFAULT_CHUNKS_PATH),
            );
        }
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
