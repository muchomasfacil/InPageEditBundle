<?php
namespace MuchoMasFacil\InPageEditBundle\Controller\ORM\Doctrine;

use Symfony\Component\DependencyInjection\ContainerAware;
use MuchoMasFacil\InPageEditBundle\Controller\ControllerInterface;

class EntityController extends ContainerAware //implements ControllerInterface
{

    public function ajaxRenderAction($ipe_hash)
    {

    }

    //no associated route. Will always be called from twig templates
    public function renderAction($ipe_definition, $object, $render_template = null, $params = array(), $render_with_container = true)
    {
        $this->render_vars['object'] = $object;

        return $this->container->get('templating')->renderResponse($render_template , $this->render_vars);
    }

    public function editIndexAction($ipe_hash)
    {

    }

}
