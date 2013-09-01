<?php
namespace MuchoMasFacil\InPageEditBundle\Twig;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class IpeOrmDoctrineFakerExtension extends \Twig_Extension
{

    /**
     *
     * @var  \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * Constructor.
     *
     * @param FragmentHandler $handler A FragmentHandler instance
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'ipe_orm_doctrine_fake' => new \Twig_Function_Method($this, 'ipeOrmDoctrineFake', array('is_safe' => array('html'))),
        );
    }

    public function ipeOrmDoctrineFake($entityName, $number = 1, $is_collection = false, $customColumnFormatters = array(), $customModifiers = array(), $generateId = false)
    {
        if ($number > 1) {
            $is_collection = true;
        }
        $generator = $this->container->get('mucho_mas_facil_in_page_edit.doctrine.orm.faker');
        $inserted_entities = $generator->ORMDoctrinePopulate($entityName, $number, $customColumnFormatters = array(), $customModifiers = array(), $generateId = false);
        $class_name = $this->container->get('doctrine')->getRepository($entityName)->getClassName();

        return ($is_collection)? $inserted_entities[$class_name] : $inserted_entities[$class_name][0];
    }

    public function getName()
    {
        return 'ipe_extension_doctrine_orm_faker';
    }

}