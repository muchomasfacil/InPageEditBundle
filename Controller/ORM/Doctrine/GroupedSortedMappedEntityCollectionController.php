<?php
namespace MuchoMasFacil\InPageEditBundle\Controller\ORM\Doctrine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Doctrine\Common\Util\Inflector;

use MuchoMasFacil\InPageEditBundle\Controller\IPEController;
use MuchoMasFacil\InPageEditBundle\Controller\IPEControllerInterface;

class GroupedSortedMappedEntityCollectionController extends IPEController implements IPEControllerInterface
{

     function __construct()
    {
        parent::__construct();

    }

    public function getObject($ipe_definition, $find_params, $params)
    {
        $find_params = $this->getFindParams($ipe_definition, $find_params, $params);
        $finder = ($find_params['is_collection'])? 'findBy' : 'findOneBy';
        $entity = $this->container->get('doctrine')
            ->getRepository($find_params['entity_class'])
            ->$finder($find_params['find_by'], $find_params['order_by']);

        //if (!$entity) {
        //    throw new \Exception($this->trans('controller.not_found_exception', array('%find_params%' => print_r($find_params, true))));
        //}
        return $entity;
    }

    public function getFindParams($ipe_definition, $find_params, $params)
    {
        $definitions = $this->container->getParameter('mucho_mas_facil_in_page_edit.definitions');
        $definition = $definitions[$ipe_definition];
        return array_merge($definition['find_params'], $find_params);
        // $ipe_handler_field = $definition['params']['collection_ipe_handler_field'];
        // $getter = 'get'.ucwords(Inflector::camelize($ipe_handler_field));

        // if (is_array($object)) {
        //     $entity_class = get_class($object[0]);
        //     $find_by = array($ipe_handler_field => $object[0]->$getter());
        //     $order_by = array($definition['params']['collection_ipe_position_field'] => 'ASC');
        //     $is_collection = true;
        // }
        // else {
        //     $entity_class = get_class($object);
        //     $find_by = array($ipe_handler_field => $object->$getter());
        //     $order_by = array();
        //     $is_collection = false;
        // }

        // return array('entity_class' => $entity_class, 'find_by' => $find_by, 'order_by' => $order_by, 'is_collection' => $is_collection);
    }

    public function editAction($ipe_hash, Request $request)
    {
        $ipe = $this->getIpe($ipe_hash);
        if ($ipe['find_params']['is_collection']) {

            return $this->collectionListAction($ipe_hash, $request);
        }
        else {
            $object = $this->getObject($ipe['ipe_definition'], $ipe['find_params'], $ipe['params']);
            $request->query->set('id', $object->getId());

            return $this->collectionEditItemAction($ipe_hash, $request);
        }
    }

    public function collectionEditItemAction($ipe_hash, Request $request)
    {
        $id = $request->query->get('id');
        $action_on_success = $request->query->get('action_on_success');
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        $params = $ipe['params'];

        $em = $this->container->get('doctrine')->getManager();
        //we are editing a single entity, so let us get it by id
        if ($id) {
            //editing entity
            $entity = $em->getRepository($ipe['find_params']['entity_class'])->find($id);
        }
        else {
            //creating new entry
            $class = $this->container->get('doctrine')->getManager()->getClassMetadata($ipe['find_params']['entity_class'])->getName();
            $entity = new $class();

            $position = $request->query->get('position');
            if ($position != null) {
                $position_setter = 'set'.Inflector::classify($params['collection_ipe_position_field']);
                $entity->$position_setter($position);

                $handler_setter = 'set'.Inflector::classify($params['collection_ipe_handler_field']);
                $handler_value = $ipe['find_params']['find_by'][$params['collection_ipe_handler_field']];
                $entity->$handler_setter($handler_value);
            }
        }

        $form_type_class = (isset($params['form_type_class']))? $params['form_type_class']: $this->guessFormTypeClass(get_class($entity));
        $form = $this->container->get('form.factory')->create(new $form_type_class(), $entity);

        $form->handleRequest($request);

        if ($form-> isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($entity);
                try{
                    $em->flush();
                    $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.editAction.entry_saved'), 'close' => true, 'use_raw' => true);
                    $this->render_vars['reload_content'] = true;
                    if ($action_on_success == 'close') {
                        $this->render_vars['data_ipe_hash'] = $ipe_hash;
                        $close_template = $this->render_vars['parent_bundle_name'] . ':' . $this->render_vars['parent_controller_name'] . ':_closeDialog.html.twig';
                        return $this->container->get('templating')->renderResponse($close_template, $this->render_vars);
                    }
                    if ($action_on_success == 'list') {
                        return $this->collectionListAction($ipe_hash, $request, true);
                    }
                }
                catch (\Exception $e)
                {
                    $this->render_vars['flashes'][] = array('type' => 'error', 'message' => $this->trans('controller.editAction.flush_errors'). ' '. $e, 'close' => true, 'use_raw' => true);
                }
            }
            else {
                $this->render_vars['flashes'][] = array('type' => 'error', 'message' => $this->trans('controller.editAction.form_errors'), 'close' => true, 'use_raw' => true);
            }
        }// isSubmitted()

        if (!isset($this->render_vars['reload_content'])) {
            $this->render_vars['reload_content'] = false;
        }
        $this->render_vars['is_collection'] = $ipe['find_params']['is_collection'];
        $this->render_vars['entity'] = $entity;
        $this->render_vars['form'] = $form->createView();
        $this->render_vars['data_ipe_hash'] = $ipe_hash;
        $this->render_vars['params'] = $params;

        return $this->container->get('templating')->renderResponse($this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':collectionEditItem.html.twig', $this->render_vars);
    }

    public function collectionListAction($ipe_hash, Request $request, $reload_content = false)
    {
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        //the entry MUST alredy exist let us get it ////////////////////////////
        $list = $this->getObject($ipe['ipe_definition'], $ipe['find_params'], $ipe['params']);

        $params = $ipe['params'];
        $entity_class = $ipe['find_params']['entity_class'];

        $class = $this->container->get('doctrine')->getManager()->getClassMetadata($entity_class)->getName();

        $list_to_string_method = (isset($params['list_to_string_method']))? $params['list_to_string_method'] : '__toString';
        $this->render_vars['has_to_string_method'] = (method_exists(new $class(), $list_to_string_method))? true : false;
        if (!$this->render_vars['has_to_string_method']) {
            $this->render_vars['flashes'][] = array('type' => 'warning', 'message' => $this->trans('controller.collectionListAction.list_to_string_method_not_defined', array('%entity_class%' => $entity_class)), 'close' => true, 'use_raw' => false);
        }
        $this->render_vars['inflected_position_field'] = Inflector::classify($params['collection_ipe_position_field']);
        $this->render_vars['reload_content'] = $reload_content;
        $this->render_vars['list'] = $list;
        $this->render_vars['data_ipe_hash'] = $ipe_hash;
        $this->render_vars['params'] = $params;
        $final_render_template = $this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':collectionList.html.twig';

        return $this->container->get('templating')->renderResponse($final_render_template , $this->render_vars);
    }

    public function collectionMoveItemAction($ipe_hash, Request $request)
    {
        $position = $request->query->get('position');
        $id = $request->query->get('id');
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        $params = $ipe['params'];
        $em = $this->container->get('doctrine')->getManager();
        $entity_class = $ipe['find_params']['entity_class'];
        $entity = $em->getRepository($entity_class)->find($id);
        if (!$entity) {
            throw new \Exception($this->trans('controller.not_found_exception', array('%entity_class%' => $entity_class ,'%find_by%' => 'id='.$id )));
        }
        $position_setter = 'set'.Inflector::classify($params['collection_ipe_position_field']);
        $entity->$position_setter($position);
        $em->persist($entity);
        $em->flush();
        $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.collectionMoveItemAction.item_moved'), 'close' => true, 'use_raw' => true);

        return $this->collectionListAction($ipe_hash, $request, true);
    }

    public function collectionRemoveItemAction($ipe_hash, Request $request)
    {
        $id = $request->query->get('id');
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        $params = $ipe['params'];
        $em = $this->container->get('doctrine')->getManager();
        $entity_class = $ipe['find_params']['entity_class'];
        $entity = $em->getRepository($entity_class)->find($id);
        if (!$entity) {
            throw new \Exception($this->trans('controller.not_found_exception', array('%entity_class%' => $entity_class ,'%find_by%' => 'id='.$id )));
        }
        $em->remove($entity);
        $em->flush();
        $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.collectionRemoveItemAction.item_removed'), 'close' => true, 'use_raw' => true);

        if (!$ipe['find_params']['is_collection']) {
            $this->render_vars['reload_content'] = true;
            $this->render_vars['data_ipe_hash'] = $ipe_hash;
            $close_template = $this->render_vars['parent_bundle_name'] . ':' . $this->render_vars['parent_controller_name'] . ':_closeDialog.html.twig';

            return $this->container->get('templating')->renderResponse($close_template, $this->render_vars);
        }

        return $this->collectionListAction($ipe_hash, $request, true);
    }

    public function collectionAddItemAction($ipe_hash, Request $request)
    {
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        $list = $this->getObject($ipe['ipe_definition'], $ipe['find_params'], $ipe['params']);
        $params = $ipe['params'];
        if (($params['max_collection_length']) && (count($list) >= $params['max_collection_length'])) {
            $this->render_vars['flashes'][] = array('type' => 'error', 'message' => $this->trans('controller.collectionAddItemAction.max_collection_length_error', array('%max_collection_length%' => $params['max_collection_length'])), 'close' => true, 'use_raw' => true);
            return $this->collectionListAction($ipe_hash, $request, false);
        }

        return $this->collectionEditItemAction($ipe_hash, $request);
    }

}
