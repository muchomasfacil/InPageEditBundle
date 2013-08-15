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

    public function getObject($ipe_definition, $find_params, $params)
    {
        if ($this->isFindParams($ipe_definition, $find_params, $params)) { //it is find_params so get the object
            $object = $this->container->get('doctrine')
                ->getRepository($find_params['entity_class'])
                ->findOneBy($find_params['find_by']);
            if (!$object) {
                throw new \Exception($this->trans('controller.not_found_exception', array('%find_params%' => print_r($find_params, true))));
            }

            return $object;
        }
        $object = $find_params;
        return $find_params; //it directly is an object
    }

    public function getFindParams($ipe_definition, $find_params, $params)
    {
        # in this definition normally, you pass an entity rather than find by params to $find_params
        # this allow to skip remake multiple queries when in your controller you use a collection of entities
        # Internally the MappedEntityController turns it into
        # {'entity_class': 'your_bundle:your_entity_class' 'find_by': {'id': entity_id } }
        if (!$this->isFindParams($ipe_definition, $find_params, $params)) { //what comes in $find_params is an object
            //let us get find_params
            $object = $find_params;
            $find_by = array('id' => $object->getId());
            $find_params = array('entity_class' => get_class($object), 'find_by' => $find_by);
        }
        if (!$this->checkFindObjectParams($ipe_definition, $find_params, $params)) {
            throw new \Exception($this->trans('controller.missing_find_by_param', array('%find_params%' => print_r($find_params, true))));
        }

        return $find_params;
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
        $entity = $this->getObject($ipe['ipe_definition'], $ipe['find_params'], $ipe['params']);
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
        $entity = $this->getObject($ipe['ipe_definition'], $ipe['find_params'], $ipe['params']);

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

    private function isFindParams($ipe_definition, $find_params, $params)
    {
        if (
            (is_array($find_params))
            && (isset($find_params['entity_class']))
            && (isset($find_params['find_by']))
            ) {
            return true;
        }
        return false;
    }

}
