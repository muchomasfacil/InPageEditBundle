<?php
namespace MuchoMasFacil\InPageEditBundle\Controller\ORM\Doctrine;

use MuchoMasFacil\InPageEditBundle\Controller\IPEController;
//use MuchoMasFacil\InPageEditBundle\Controller\IpeControllerInterface;

class EntityController extends IPEController //implements ControllerInterface
{

    function __construct()
    {
        parent::__construct();

    }

    //this should always by called for IpeController:ajaxIpeRenderAction
    public function ajaxRenderAction($ipe, $ipe_hash)
    {
        $object = $this->container->get('doctrine')
            ->getRepository($ipe['find_object_params']['entity_class'])
            ->find($ipe['find_object_params']['id']);

        return $this->forward($this->render_vars['bundle_name'] . ':'.$this->render_vars['controller_name'].':render', array(
            'ipe_definition'  => $ipe['ipe_definition'],
            'object'  => $object,
            'render_template' => $ipe['render_template'],
            'params' => $ipe['params'],
            'render_with_container' => false
        ));
    }

    public function getFindObjectParams($ipe_definition, $object, $render_template, $params , $render_with_container)
    {
        return array('entity_class' => get_class($object), 'id' => $object->getId());
    }

    public function editAction($ipe_hash, $action_on_success = null, $id = null)
    {
        $request = $this->container->get('request');
        //let us get ipe params from ipe_hash session
        $ipe = $this->getIpe($ipe_hash);
        $params = $ipe['params'];
        // let us get definitions
        $this->checkRoles($ipe['editor_roles']);

        $request = $this->container->get('request');
        $em = $this->container->get('doctrine')->getManager();

        //the entry MUST alredy exist let us get it ////////////////////////////
        $entity = $em
            ->getRepository($ipe['find_object_params']['entity_class'])
            ->find($ipe['find_object_params']['id']);

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

    public function createDataIpeHash($ipe)
    {
        return md5(serialize($ipe));
    }


}
