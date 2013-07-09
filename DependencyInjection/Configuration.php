<?php

namespace MuchoMasFacil\InPageEditBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('mucho_mas_facil_in_page_edit');

        /*$rootNode
            ->children()
                ->scalarNode('content_orm')->end()
            ->end()
        ;*/

        $rootNode
            ->children()
                ->arrayNode('available_langs')
                    ->useAttributeAsKey('locale')
                    ->prototype('array')
                        ->children()
                        ->scalarNode('label')->end()
                    ->end()
                ->end()
            ->end()
        ;

        $rootNode
            ->children()
                ->arrayNode('definitions')
                    ->useAttributeAsKey('name')
                    ->prototype('variable')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
