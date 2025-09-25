<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class Configuration implements ConfigurationInterface
{
    public const DEFAULT_UPLOADED_DIGITAL_FILE_DIRECTORY = __DIR__ . '/../../var/uploads/digital_files';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_digital_product_plugin');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        /** @phpstan-ignore-next-line  */
        $rootNode
            ->children()
                ->scalarNode('uploaded_digital_file_directory')
                    ->cannotBeEmpty()
                    ->defaultValue(self::DEFAULT_UPLOADED_DIGITAL_FILE_DIRECTORY)
                    ->validate()
                        ->always(function ($value) {
                            if (!is_string($value)) {
                                throw new InvalidConfigurationException('uploaded_digital_file_directory must be string');
                            }

                            return $value;
                        })
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
