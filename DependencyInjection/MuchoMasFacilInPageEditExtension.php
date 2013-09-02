<?php

namespace MuchoMasFacil\InPageEditBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MuchoMasFacilInPageEditExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        //first load services and definitions
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('definitions.yml');

        //with definitios we make a mergue
        //and add them to our list
        $parameter_configs[] = array(
            'definitions'  => $container->getParameter('mucho_mas_facil_in_page_edit.definitions'),
            'message_catalog'  => $container->getParameter('mucho_mas_facil_in_page_edit.message_catalog'),
            'default_ipe_locale'  => $container->getParameter('mucho_mas_facil_in_page_edit.default_ipe_locale'),
            'available_langs'  => $container->getParameter('mucho_mas_facil_in_page_edit.available_langs'),
            );

        $configuration = new Configuration();
        $final_config = $this->processConfiguration($configuration, array_merge($parameter_configs, $configs));
        // as we want the available_langs to overwrite and not to merge what comes in parameter_configs
        // (and the sintax has alredy been checked )
        if (isset($configs[0]['available_langs'])) {
            $final_config['available_langs'] = $configs[0]['available_langs'];
        }

        $container->setParameter('mucho_mas_facil_in_page_edit.definitions', $final_config['definitions']);
        $container->setParameter('mucho_mas_facil_in_page_edit.message_catalog', $final_config['message_catalog']);
        $container->setParameter('mucho_mas_facil_in_page_edit.default_ipe_locale', $final_config['default_ipe_locale']);
        $container->setParameter('mucho_mas_facil_in_page_edit.available_langs', $final_config['available_langs']);
    }
}
