<?php
namespace MuchoMasFacil\InPageEditBundle\Controller\ORM\Doctrine;

use MuchoMasFacil\InPageEditBundle\Controller\IpeController;
//use MuchoMasFacil\InPageEditBundle\Controller\IpeControllerInterface;

class EntityController extends IpeController //implements ControllerInterface
{

    function __construct()
    {
        parent::__construct();

    }

    //this should always by called for IpeController:ajaxIpeRenderAction
    public function ajaxRenderAction($ipe)
    {
        $object = $this->container->get('doctrine')
            ->getRepository($ipe['find_object_params']['class'])
            ->find($ipe['find_object_params']['id']);

        return $this->forward($this->render_vars['bundle_name'] . ':'.$this->render_vars['controller_name'].':render', array(
            'ipe_definition'  => $ipe['ipe_definition'],
            'object'  => $object,
            'render_template' => $ipe['render_template'],
            'params' => $ipe['params'],
            'render_with_container' => false
        ));
    }

    //no associated route. Will always be called from twig templates or from ajaxRenderAction
    public function renderAction($ipe_definition, $object, $render_template, $params = array(), $render_with_container = true)
    {
        $definitions = $this->container->getParameter('mucho_mas_facil_in_page_edit.definitions');
        $this->getIpeLocale();
        $definition = $definitions[$ipe_definition];
        $params = array_merge($definition['params'], $params);

        $this->render_vars['object'] = $object;
        $this->render_vars['params'] = $params;

        if (!$render_with_container) {
            $final_render_template = $render_template;
        }
        else {

            $ipe = array(
                'ipe_definition' => $ipe_definition,
                'render_template' => $render_template,
                'params' => $params,
                //this should be specific by definition
                'find_object_params' => array('class' => get_class($object), 'id' => $object->getId())
            );
            $ipe_hash = $this->createDataIpeHash($ipe);

            $this->render_vars['editor_roles'] = (isset($params['editor_roles']))? $params['editor_roles']: $definition['editor_roles'];
            $this->render_vars['container_html_tag'] = (isset($params['container_html_tag']))? $params['container_html_tag']: $definition['container_html_tag'];
            $this->render_vars['container_html_attributes'] = (isset($params['container_html_attributes']))? $params['container_html_attributes']: $definition['container_html_attributes'];
            $this->render_vars['render_template'] = $render_template;
            $this->render_vars['data_ipe_hash'] = $ipe_hash;
            $this->render_vars['show_data_ipe_hash'] = $this->ipeIsGranted($this->render_vars['editor_roles']);

            $final_render_template = $this->render_vars['parent_bundle_name'] . ':' . $this->render_vars['parent_controller_name'] . ':render.html.twig';
            //now ipe to session
            $this->setIpe($ipe_hash, $ipe);
        }

        return $this->container->get('templating')->renderResponse($final_render_template , $this->render_vars);
    }

    public function editIndexAction($ipe_hash)
    {

    }

    public function createDataIpeHash($ipe)
    {
        return md5(serialize($ipe));
    }


}
