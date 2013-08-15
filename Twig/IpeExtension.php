<?php
namespace MuchoMasFacil\InPageEditBundle\Twig;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class IpeExtension extends \Twig_Extension
{

    /**
     *
     * @var  \Symfony\Component\DependencyInjection\Container
     */
    private $handler;

    private $definitions;

    /**
     * Constructor.
     *
     * @param FragmentHandler $handler A FragmentHandler instance
     */
    public function __construct(FragmentHandler $handler, $definitions)
    {
        $this->handler = $handler;
        $this->definitions = $definitions;
    }

    public function getFunctions()
    {
        return array(
            'ipe_render'            => new \Twig_Function_Method($this, 'ipeRenderFragment',  array('is_safe' => array('html'))),
        );
    }

    public function ipeRenderFragment($ipe_definition, $find_params, $render_template, $params = array(), $render_with_container = true)
    {
        // let us check we have a valid definition
        if (!in_array($ipe_definition, array_keys($this->definitions))) {
            // no definition, let us search for an alias
            foreach ($this->definitions as $key => $value) {
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
        $definition = $this->definitions[$ipe_definition];
        $options = array(
            'ipe_definition'  => $ipe_definition,
            'find_params'  => $find_params,
            'render_template' => $render_template,
            'params' => $params,
            'render_with_container' => $render_with_container,
            );

        return $this->renderFragment($this->controller($definition['ipe_controller'].':render', $options));
    }

    public function getName()
    {
        return 'ipe_extension';
    }

    private function renderFragment($uri, $options = array())
    {
        $strategy = isset($options['strategy']) ? $options['strategy'] : 'inline';
        unset($options['strategy']);

        return $this->handler->render($uri, $strategy, $options);
    }

    private function controller($controller, $attributes = array(), $query = array())
    {
        return new ControllerReference($controller, $attributes, $query);
    }
}