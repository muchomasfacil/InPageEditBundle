<?php
namespace MuchoMasFacil\InPageEditBundle\Controller\ORM\Doctrine;

use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Util\Inflector;

use MuchoMasFacil\InPageEditBundle\Controller\IPEController;
use MuchoMasFacil\InPageEditBundle\Controller\IPEControllerInterface;



class SortedMappedEntityCollectionController extends IPEController implements IPEControllerInterface
{

    function __construct()
    {
        parent::__construct();

    }

    public function findObject($find_object_params)
    {
        $entity = $this->container->get('doctrine')
            ->getRepository($find_object_params['entity_class'])
            ->findBy($find_object_params['find_by'], $find_object_params['order_by']);
        if (!$entity) {
            throw new \Exception($this->trans('controller.not_found_exception', array('%find_object_params%' => print_r($find_object_params, true))));
        }
        return $entity;
    }

    public function getFindObjectParams($ipe_definition, $object, $render_template, $params , $render_with_container)
    {
        $definitions = $this->container->getParameter('mucho_mas_facil_in_page_edit.definitions');
        $definition = $definitions[$ipe_definition];
        $ipe_handler_field = $definition['params']['collection_ipe_handler_field'];
        $getter = 'get'.ucwords(Inflector::camelize($ipe_handler_field));
        $order_by = array($definition['params']['collection_ipe_position_field'] => 'ASC');
        $find_by = array($ipe_handler_field => $object[0]->$getter());
        return array('entity_class' => get_class($object[0]), 'find_by' => $find_by, 'order_by' => $order_by);
    }

    public function editAction($ipe_hash)
    {
        return $this->collectionListAction($ipe_hash);
    }

    public function collectionListAction($ipe_hash, $reload_content = false)
    {
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        $object = $this->findObject($ipe['find_object_params']);
        $params = $ipe['params'];
        $em = $this->container->get('doctrine')->getManager();
        //the entry MUST alredy exist let us get it ////////////////////////////
        $list = $this->findObject($ipe['find_object_params']);
        $entity_class = $ipe['find_object_params']['entity_class'];
        $this->render_vars['has_to_string_method'] = (method_exists(new $entity_class(), '__toString'))? true : false;
        if (!$this->render_vars['has_to_string_method']) {
            $this->render_vars['flashes'][] = array('type' => 'warning', 'message' => $this->trans('controller.collectionListAction.to_string_not_defined', array('%entity_class%' => $entity_class)), 'close' => true, 'use_raw' => false);
        }
        $this->render_vars['inflected_position_field'] = Inflector::classify($params['collection_ipe_position_field']);
        $this->render_vars['reload_content'] = $reload_content;
        $this->render_vars['list'] = $list;
        $this->render_vars['data_ipe_hash'] = $ipe_hash;
        $this->render_vars['params'] = $params;
        $final_render_template = $this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':collectionList.html.twig';

        return $this->container->get('templating')->renderResponse($final_render_template , $this->render_vars);
    }

}
