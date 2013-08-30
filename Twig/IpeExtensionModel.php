<?php
namespace MuchoMasFacil\InPageEditBundle\Twig;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Session\Session;

class IpeExtensionModel extends \Twig_Extension
{

    protected function renderFragment(FragmentHandler $handler, $uri, $options = array())
    {
        $strategy = isset($options['strategy']) ? $options['strategy'] : 'inline';
        unset($options['strategy']);

        return $handler->render($uri, $strategy, $options);
    }

    protected function controller($controller, $attributes = array(), $query = array())
    {
        return new ControllerReference($controller, $attributes, $query);
    }

    protected function getCheckIpeDefinition($ipe_definition, $definitions)
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

    protected function checkFindObjectParams($ipe_definition, $definitions, $find_params, $params)
    {        
        $definition = $definitions[$ipe_definition];    
        foreach ($definition['find_params'] as $key => $value) {
            if ((!is_array($find_params)) || (!isset($find_params[$key]))) {
                throw new \Exception('Not found required find_params: '. $key);
            }
        }
    }

    public function getName()
    {
        return 'ipe_extension_model';
    }
}