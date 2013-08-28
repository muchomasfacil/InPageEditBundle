<?php
namespace MuchoMasFacil\InPageEditBundle\Twig;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Session\Session;

class IpeExtension extends \Twig_Extension
{

    /**
     *
     * @var  \Symfony\Component\DependencyInjection\Container
     */
    private $session;

    private $translator;

    private $handler;

    private $definitions;

    private $message_catalog;

    /**
     * Constructor.
     *
     * @param FragmentHandler $handler A FragmentHandler instance
     */
    public function __construct(Session $session, Translator $translator, FragmentHandler $handler, $definitions, $message_catalog)
    {
        $this->session = $session;
        $this->translator = $translator;
        $this->handler = $handler;
        $this->definitions = $definitions;
        $this->message_catalog = $message_catalog;
    }

    public function getFunctions()
    {
        return array(
            'ipe_render'            => new \Twig_Function_Method($this, 'ipeRenderFragment',  array('is_safe' => array('html'))),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ipe_trans', array($this, 'ipe_trans')),
        );
    }

    public function ipe_trans($translatable, $params = array(), $message_catalog = null, $ipe_locale = null)
    {
        if (empty($message_catalog)) {
            $message_catalog = $this->message_catalog;
        }
        if (empty($ipe_locale)) {
            $ipe_locale = $this->session->get('ipe_locale');
        }
        return $this->translator->trans($translatable, $params, $message_catalog, $ipe_locale);
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