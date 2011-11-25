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
        
        $rootNode
            ->children()
                ->arrayNode('entity_custom_params')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('editor_roles')->end()
                        ->scalarNode('render_action')->end()
                        ->scalarNode('render_template')->end()
                        ->scalarNode('form_template')->end()
                        ->scalarNode('orm')->end()
                    ->end()
                ->end()
            ->end()
        ;

        /*$rootNode
            ->children()
                ->scalarNode('content_orm')->end()
            ->end()
        ;*/

        $rootNode
            ->children()
                ->arrayNode('content_definitions')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('content_entity_class')->isRequired()->end()                   
                        ->scalarNode('editor_roles')->end()
                        ->booleanNode('is_collection')->end()
                        ->scalarNode('max_collection_length')->end()
                        ->scalarNode('lorem_ipsum_items_in_collection')->end()
                        ->scalarNode('render_action')->end()
                        ->scalarNode('render_template')->end()
                        ->scalarNode('form_template')->end()
                    ->end()
                ->end()
            ->end()
        ;

        $rootNode
            ->children()
                ->arrayNode('ckeditor_options')
                ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
