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

    public function ipeRenderFuncion()
    {
        $param_definitions = $this->container->getParameter('mucho_mas_facil_in_page_edit.definitions');

        //return $this->container;
        return $param_definitions;
    }

    public function getName()
    {
        return 'ipe_extension';
    }
}