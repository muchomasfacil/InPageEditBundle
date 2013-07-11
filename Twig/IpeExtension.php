<?php
namespace MuchoMasFacil\InPageEditBundle\Twig;

class IpeExtension extends \Twig_Extension
{

    /**
     *
     * @var  \Symfony\Component\DependencyInjection\Container
     */
    protected $container;


    public function __construct(\Symfony\Component\DependencyInjection\Container $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ipe_render', array($this, 'ipeRenderFuncion')),
        );
    }

    public function ipeRenderFuncion($ipe_definition, $object, $render_template, $params = array(), $render_with_container = true)
    {
        $param_definitions = $this->container->getParameter('mucho_mas_facil_in_page_edit.definitions');
        // let us check we have a valid definition
        if (!in_array($ipe_definition, $param_definitions)) {
            // let us look for aliases
            $aliases = array_column($param_definitions , 'alias');
            print_r($aliases);
            die();
        }

        //return $this->container;
        return $param_definitions;
    }

    public function getName()
    {
        return 'ipe_extension';
    }
}