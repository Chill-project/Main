<?php

namespace Chill\MainBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('chill_main');

        $rootNode
            ->children()
                ->scalarNode('installation_name')
                    ->cannotBeEmpty()
                    ->defaultValue('Chill')
                ->end()
                ->arrayNode('available_languages')
                    ->defaultValue(array('fr'))
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('routing')
                    ->children()
                        ->arrayNode('resources')
                        ->prototype('scalar')->end()
                        ->end()
                ->end()
                ->end()
                ->arrayNode('available_roles')
                    ->defaultValue(array())
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
