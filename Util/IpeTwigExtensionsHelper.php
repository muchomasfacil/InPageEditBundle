<?php
namespace MuchoMasFacil\InPageEditBundle\Util;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class IpeTwigExtensionsHelper
{

    public static function createIpe($ipe_definition, $definitions, $find_params, $render_template, $params = array())
    {
        //check we have a valid definition or alias
        $ipe_definition = self::getCheckIpeDefinition($ipe_definition, $definitions);
        $definition = $definitions[$ipe_definition];

        //let us merge definitions params with call custom params
        $params = array_merge($definition['params'], $params);

        // and check find_params are correct, according to definition
        $find_params = self::getCheckFindParams($ipe_definition, $definitions, $find_params);
        //now create the var to store in session
        return array(
                'ipe_definition' => $ipe_definition,
                'find_params' => $find_params,
                'render_template' => $render_template,
                'params' => $params,
            );
    }

    public static function getCheckIpeDefinition($ipe_definition, $definitions)
    {
        if (!in_array($ipe_definition, array_keys($definitions))) {
            // no definition, let us search for an alias
            foreach ($definitions as $key => $value) {
                if (isset($value['alias'])) {
                    $aliases[$key] = $value['alias'];
                }
            }
            if (($key = array_search($ipe_definition, $aliases)) === false) {
                throw new \Exception ($ipe_definition . ' is not a valid ipe definition or ipe definition alias');
            }
            else {
                $ipe_definition = $key;
            }
        }
        return $ipe_definition;
    }

    public static function getCheckFindParams($ipe_definition, $definitions, $find_params)
    {
        $definition = $definitions[$ipe_definition];
        //let us merge definitions params with call custom params
        $find_params = array_merge($definition['find_params'], $find_params);
        foreach ($definition['find_params'] as $key => $value) {
            if ((!is_array($find_params)) || (!isset($find_params[$key]))) {
                throw new \Exception('Not found required find_params: '. $key);
            }
        }
        return $find_params;
    }

    public static function createHashForObject($object)
    {
        return md5(serialize($object));
    }

    public static function renderFragment(FragmentHandler $handler, $uri, $options = array())
    {
        $strategy = isset($options['strategy']) ? $options['strategy'] : 'inline';
        unset($options['strategy']);

        return $handler->render($uri, $strategy, $options);
    }

    public static function controller($controller, $attributes = array(), $query = array())
    {
        return new ControllerReference($controller, $attributes, $query);
    }

        public static function  getTitleHandler($requestUri, $baseUrl)
    {
        echo '---'. md5(str_replace($baseUrl, '', $requestUri)) . '__title_tag';
        return md5(str_replace($baseUrl, '', $requestUri)) . '__title_tag';

    }

    public static function  getTitleFindByParams($requestUri, $baseUrl, $collection_ipe_handler_field = 'ipe_handler')
    {
        return  array($collection_ipe_handler_field => self::getTitleHandler($requestUri, $baseUrl));
    }

    public static function  getTitleFindParams($requestUri, $baseUrl, $collection_ipe_handler_field = 'ipe_handler')
    {
        return array(
                'entity_class' => 'MuchoMasFacilInPageEditBundle:GroupedSortedMappedString',
                'find_by' =>  self::getTitleFindByParams($requestUri, $baseUrl, $collection_ipe_handler_field),
                'is_collection' => false ,
            );
    }

}