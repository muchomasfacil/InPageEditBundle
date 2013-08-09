<?php
namespace MuchoMasFacil\InPageEditBundle\Controller\ORM\Doctrine;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use MuchoMasFacil\InPageEditBundle\Controller\IPEController;
use MuchoMasFacil\InPageEditBundle\Controller\IPEControllerInterface;



class MappedEntityController extends IPEController implements IPEControllerInterface
{

    function __construct()
    {
        parent::__construct();

    }

    public function findObject($find_object_params)
    {
        $entity = $this->container->get('doctrine')
            ->getRepository($find_object_params['entity_class'])
            ->findOneBy($find_object_params['find_by']);

        if (!$entity) {
            throw new \Exception($this->trans('controller.not_found_exception', array('%find_object_params%' => print_r($find_object_params, true))));
        }
        return $entity;
    }

    public function getFindObjectParams($ipe_definition, $object_or_find_object_params, $render_template, $params , $render_with_container)
    {
        if ($this->isFindObjectParams($object_or_find_object_params)) { //it directly is find_object_params
            return $object_or_find_object_params;
        }
        $object = $object_or_find_object_params;
        $find_by = array('id' => $object->getId());

        return array('entity_class' => get_class($object), 'find_by' => $find_by);
    }

    public function isFindObjectParams($object_or_find_object_params)
    {
        if (
            (is_array($object_or_find_object_params))
            && (isset($object_or_find_object_params['entity_class']))
            && (isset($object_or_find_object_params['find_by']))
            ) {
            return true;
        }
        return false;
    }

    public function editAction($ipe_hash, Request $request)
    {
        $action_on_success = $request->query->get('action_on_success');
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $this->checkRoles($ipe['editor_roles']);
        $params = $ipe['params'];

        $em = $this->container->get('doctrine')->getManager();

        //the entry MUST alredy exist let us get it ////////////////////////////
        $entity = $this->findObject($ipe['find_object_params']);

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
        //return new Response('edit'.$ipe_hash);

        return $this->container->get('templating')->renderResponse($this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':edit.html.twig', $this->render_vars);
    }

    public function removeAction($ipe_hash, Request $request)
    {
        $action_on_success = $request->query->get('action_on_success');
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        // let us get definitions
        $this->checkRoles($ipe['editor_roles']);

        $em = $this->container->get('doctrine')->getManager();

        //the entry MUST alredy exist let us get it ////////////////////////////
        $entity = $this->findObject($ipe['find_object_params']);

        $em->remove($entity);
        $em->flush();
        $session = $this->container->get('request')->getSession();
        $this->removeIpe($ipe_hash);
        if ($action_on_success == 'close') {
            $this->render_vars['data_ipe_hash'] = $ipe_hash;
            $this->render_vars['remove_content_container'] = true;
            $close_template = $this->render_vars['parent_bundle_name'] . ':' . $this->render_vars['parent_controller_name'] . ':_closeDialog.html.twig';
            return $this->container->get('templating')->renderResponse($close_template, $this->render_vars);
        }
        //////////////////////pendiente
        $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.collectionDeleteItemAction.entry_deleted'), 'close' => true, 'use_raw' => true);

        $close_template = $this->render_vars['parent_bundle_name'] . ':' . $this->render_vars['parent_controller_name'] . ':_closeDialog.html.twig';

        return $this->container->get('templating')->renderResponse($close_template, $this->render_vars);
    }

}
