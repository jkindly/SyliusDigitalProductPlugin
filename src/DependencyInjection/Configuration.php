<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_digital_product_plugin');
        $rootNode = $treeBuilder->getRootNode();
        /** @phpstan-ignore-next-line  */
        $rootNode
            ->children()
                ->scalarNode('uploaded_digital_file_directory')
                    ->cannotBeEmpty()
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
