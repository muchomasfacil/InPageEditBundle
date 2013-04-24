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
                    ->prototype('array')
                    ->children()
                        ->scalarNode('entity_class')->end()
                        ->scalarNode('form_type_class')->end()
                        ->scalarNode('render_template')->end()
                        ->scalarNode('ipe_controller')->end()
                        ->booleanNode('is_collection')->end()
                        ->scalarNode('max_collection_length')->end()
                        ->scalarNode('number_of_entities_to_fake_if_collection')->end()
                        ->scalarNode('collection_ipe_handler_field')->end()
                        ->scalarNode('collection_ipe_position_field')->end()
                        ->scalarNode('faker_locale')->end()
                        ->arrayNode('faker_custom_column_formatters')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('faker_custom_modifiers')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('faker_generate_id')->end()
                        ->arrayNode('editor_roles')
                            ->prototype('scalar')->end()
                        ->end()                                                
                        ->scalarNode('container_html_tag')->end()                        
                        ->scalarNode('container_html_attributes')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
