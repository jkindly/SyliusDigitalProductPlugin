<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\DependencyInjection;

use Jkindly\SyliusDigitalProductPlugin\Mailer\Emails;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Yaml\Yaml;

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
        $this->prependFlysystemConfig($container);
        $this->prependMailerConfig($container);
        $this->prependTwigHooksConfig($container);
        $this->prependSerializerConfig($container);
        $this->prependValidatorConfig($container);
    }

    private function prependFlysystemConfig(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('flysystem', [
            'storages' => [
                'sylius_digital_product.storage.uploaded_file' => [
                    'adapter' => 'local',
                    'options' => [
                        'directory' => '%sylius_digital_product_plugin.uploaded_file.product_files_path%',
                    ],
                ],
                'sylius_digital_product.storage.order_file' => [
                    'adapter' => 'local',
                    'options' => [
                        'directory' => '%sylius_digital_product_plugin.uploaded_file.order_files_path%',
                    ],
                ],
                'sylius_digital_product.storage.chunks' => [
                    'adapter' => 'local',
                    'options' => [
                        'directory' => '%sylius_digital_product_plugin.uploaded_file.chunks_path%',
                    ],
                ],
            ],
        ]);
    }

    private function prependMailerConfig(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('sylius_mailer', [
            'emails' => [
                Emails::DIGITAL_DOWNLOAD => [
                    'subject' => 'sylius_digital_product.email.digital_download.subject',
                    'template' => '@SyliusDigitalProductPlugin/email/digital_download.html.twig',
                ],
            ],
        ]);
    }

    private function prependTwigHooksConfig(ContainerBuilder $container): void
    {
        $this->prependYamlExtensionConfig(
            $container,
            'sylius_twig_hooks',
            __DIR__ . '/../../config/twig_hooks',
        );
    }

    private function prependSerializerConfig(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'serializer' => [
                'mapping' => [
                    'paths' => [
                        __DIR__ . '/../../config/serialization',
                    ],
                ],
            ],
        ]);
    }

    private function prependValidatorConfig(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'validation' => [
                'mapping' => [
                    'paths' => [
                        __DIR__ . '/../../config/validator',
                    ],
                ],
            ],
        ]);
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
        return 'Jkindly\SyliusDigitalProductPlugin\Migrations';
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

    private function prependYamlExtensionConfig(ContainerBuilder $container, string $extensionAlias, string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            if (!$file->isFile() || 'yaml' !== $file->getExtension()) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        foreach ($files as $file) {
            $config = Yaml::parseFile($file, Yaml::PARSE_CONSTANT);

            if (!is_array($config) || !isset($config[$extensionAlias]) || !is_array($config[$extensionAlias])) {
                continue;
            }

            $container->prependExtensionConfig($extensionAlias, $config[$extensionAlias]);
        }
    }
}
