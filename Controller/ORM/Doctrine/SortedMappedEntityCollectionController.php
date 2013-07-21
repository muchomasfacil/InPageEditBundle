<?php
namespace MuchoMasFacil\InPageEditBundle\Controller\ORM\Doctrine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

        //if (!$entity) {
        //    throw new \Exception($this->trans('controller.not_found_exception', array('%find_object_params%' => print_r($find_object_params, true))));
        //}
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

    public function editAction($ipe_hash, Request $request)
    {
        return $this->collectionListAction($ipe_hash, $request);
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
            $entity = $em->getRepository($ipe['find_object_params']['entity_class'])->find($id);
        }
        else {
            $entity = new $ipe['find_object_params']['entity_class']();
            $position = $request->query->get('position');
            if ($position != null) {
                $position_setter = 'set'.Inflector::classify($params['collection_ipe_position_field']);
                $entity->$position_setter($position);

                $handler_setter = 'set'.Inflector::classify($params['collection_ipe_handler_field']);
                $handler_value = $ipe['find_object_params']['find_by'][$params['collection_ipe_handler_field']];
                $entity->$handler_setter($handler_value);
            }
        }

        $form_type_class = (isset($params['form_type_class']))? $params['form_type_class']: $this->guessFormTypeClass($entity);
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
        $this->render_vars['entity'] = $entity;
        $this->render_vars['form'] = $form->createView();
        $this->render_vars['data_ipe_hash'] = $ipe_hash;
        $this->render_vars['params'] = $params;

        return $this->container->get('templating')->renderResponse($this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':collectionEditItem.html.twig', $this->render_vars);
    }

    public function collectionListAction($ipe_hash, Request $request, $reload_content = false)
    {
        //$reload_content = $request->query->get('reload_content');
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        $object = $this->findObject($ipe['find_object_params']);
        $params = $ipe['params'];
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

    public function collectionMoveItemAction($ipe_hash, Request $request)
    {
        $position = $request->query->get('position');
        $id = $request->query->get('id');
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        $params = $ipe['params'];
        $em = $this->container->get('doctrine')->getManager();
        $entity_class = $ipe['find_object_params']['entity_class'];
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
        $entity_class = $ipe['find_object_params']['entity_class'];
        $entity = $em->getRepository($entity_class)->find($id);
        if (!$entity) {
            throw new \Exception($this->trans('controller.not_found_exception', array('%entity_class%' => $entity_class ,'%find_by%' => 'id='.$id )));
        }
        $em->remove($entity);
        $em->flush();
        $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.collectionRemoveItemAction.entry_removed'), 'close' => true, 'use_raw' => true);

        return $this->collectionListAction($ipe_hash, $request, true);
    }

    public function collectionAddItemAction($ipe_hash, Request $request)
    {
        $position = $request->query->get('position');

        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        //$object = $this->findObject($ipe['find_object_params']);
        $params = $ipe['params'];
        $entity_class = $ipe['find_object_params']['entity_class'];

/*
        // TODO
        list($number_of_entities, $locale, $column_formatters) = $this->getFakeDefaults($params);

        $column_formatters[$params['collection_ipe_position_field']] = $position;
        $rep = $this->container->get('doctrine')->getRepository($entity_class);
        $rep->fake($locale, 1, $column_formatters, $params['faker_custom_modifiers'], $params['faker_generate_id'] );
*/
        $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.collectionAddItemAction.item_added'), 'close' => true, 'use_raw' => true);

        //return new Response($ipe_hash. '--' . $id. '--' . $position);
        return $this->collectionListAction($ipe_hash, $request, true);
    }




}
