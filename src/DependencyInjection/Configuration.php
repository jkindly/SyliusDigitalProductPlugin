<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public const DEFAULT_UPLOADED_DIGITAL_FILE_DIRECTORY = __DIR__ . '/../../var/uploads/digital_files';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_digital_product');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        /** @phpstan-ignore-next-line  */
        $rootNode
            ->children()
                ->arrayNode('uploaded_file')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('directory')
                            ->cannotBeEmpty()
                            ->defaultValue(self::DEFAULT_UPLOADED_DIGITAL_FILE_DIRECTORY)
                            ->validate()
                                ->ifTrue(function ($v) { return !is_string($v); })
                                ->thenInvalid('uploaded_file.directory must be a string')
                            ->end()
                        ->end()
                        ->booleanNode('delete_from_storage_on_remove')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
