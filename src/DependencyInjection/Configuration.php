<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
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
