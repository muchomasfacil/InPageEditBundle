<?php
namespace MuchoMasFacil\InPageEditBundle\Util;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class IpeTwigExtensionsHelper
{

    // public static function setIpe($session, $ipe_hash, $ipe)
    // {
    //     $session->set('ipe_' . $ipe_hash, $ipe);
    // }

    // public static function getIpe($session, $ipe_hash)
    // {
    //     $ipe = $session->get('ipe_'.$ipe_hash, null);
    //     if (is_null($ipe)) {
    //         throw new \Exception('No ipe entry found for hash: '. $ipe_hash);
    //     }

    //     return $ipe;
    // }

    // protected function removeIpe($ipe_hash)
    // {
    //     $session = $this->container->get('request')->getSession();
    //     $session->remove('ipe_' . $ipe_hash);
    // }

    public static function getIpe($session, $ipe_hash)
    {
        $ipe_session = $session->get('ipe');
        if (!isset($ipe_session[$ipe_hash])) {
            throw new \Exception('No ipe session entry found for hash: '. $ipe_hash);
        }

        return $ipe_session[$ipe_hash];
    }

    public static function setIpe($session, $ipe_hash, $ipe)
    {
        $ipe_session = $session->get('ipe');
        $ipe_session[$ipe_hash] = $ipe;
        $session->set('ipe', $ipe_session);
    }

    public static function removeIpe($session, $ipe_hash)
    {
        $ipe_session = $session->get('ipe');
        if (isset($ipe_session[$ipe_hash])) {
            unset($ipe_session[$ipe_hash]);
        }
        $session->set('ipe', $ipe_session);
    }

    public static function cleanAllIpe($session)
    {
        $session->remove('ipe');
    }

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

    public static function getHashForRoute($request, $add_query_params = false)
    {
        $handler['route'] = $request->attributes->get('_route');
        $handler['route_params'] = $request->attributes->get('_route_params');
        $handler['locale'] = $request->getLocale();
        if ($add_query_params) {
            $handler['query'] = $request->query->all();
        }

        return self::createHashForObject($handler);
    }

}