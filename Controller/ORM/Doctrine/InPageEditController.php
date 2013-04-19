<?php

namespace MuchoMasFacil\InPageEditBundle\Controller\ORM\Doctrine;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\HttpFoundation\Response;
use MuchoMasFacil\InPageEditBundle\Form\FooType;
use Doctrine\Common\Util\Inflector;

class InPageEditController extends ContainerAware
{

    private $render_vars = array();

    function __construct()
    {
        $this->render_vars['bundle_name'] = 'MuchoMasFacilInPageEditBundle';        
        $this->render_vars['controller_name'] = 'ORM/Doctrine/InPageEdit';        
        $this->render_vars['ipe_message_catalog'] = 'mmf_ipe';
    }

    //this has an associated route will always be called from it
    public function ajaxRenderAction($ipe_hash)
    {
        $session = $this->container->get('request')->getSession();        
        $ipe_params = $session->get('ipe_'.$ipe_hash, null);

        if (is_null($ipe_params)) {
            throw new \Exception('No ipe entry found for hash: '. $ipe_hash);                
        } 
        return $this->forward($this->render_vars['bundle_name'] . ':'.$this->render_vars['controller_name'].':render', array(
        'find_by'  => $ipe_params['find_by']
        , 'entity_class_or_definition'  => $ipe_params['entity_class_or_definition']
        , 'render_template' => $ipe_params['render_template']
        , 'render_with_container' => false
        , 'params' => $ipe_params
            ));
    }
    
    //no associated route. Will always be called from twig templates
    public function renderAction($find_by = null, $entity_class_or_definition = null, $preloaded_result = null, $render_template = null, $create_if_not_found = false, $render_with_container = true, $params = array())
    {  
        //$param_definitions = $this->container->getParameter('mucho_mas_facil_in_page_edit.entity_custom_params');    
        $param_definitions = array(
        'default' => array(
            'entity_class' => null //guessed either from definition or by parameter
            ,'form_type_class' => null //guessed either from definition or by parameter
            , 'render_template' => null //if not set will be guessed from entity_class
            , 'ipe_controller'=> 'MuchoMasFacil\InPageEditBundle\Controller\ORM\Doctrine'                
            , 'is_collection'=> false
            , 'max_collection_length'=> null
            , 'number_of_entities_to_fake_if_collection'=> null
            , 'collection_ipe_handler_field'=> 'ipe_handler'
            , 'collection_ipe_position_field'=> 'ipe_position'
            , 'faker_locale'=> null
            , 'faker_custom_column_formatters' => array()
            , 'faker_custom_modifiers' => array()
            , 'faker_generate_id' => false
            , 'editor_roles'=> 'ROLE_USER'
            , 'container_html_tag' => 'div'
            , 'container_html_attributes' => ''
            )
        );
        
        //some magic to allow preload of contents for for example a pagination for entities
        //we make sure $find_by $entity_class.. and $preloaded result are correlationated
        //TODO CHECK IT WORKS
        
        if($preloaded_result) { 
            if (!is_array($preloaded_result)) { //it is a single entity we guess $entity_class_or_definition as a class and corresponding find_by            
                $entity_class_or_definition = get_class($preloaded_result); //so, it is an entity not a definition
                $em = $this->container->get('doctrine')->getManager($entity_class_or_definition); 
                $metadata = $em->getMetadataFactory()->getMetadataFor($entity_class_or_definition);
                $find_by = $metadata->getIdentifierValues($preloaded_result);
            }
            else { //it is a collection so find_by and $entity_class_or_definition must be set
                if ((is_null($find_by)) || (is_null($entity_class_or_definition))) {
                    throw new \Exception($this->trans('controller.renderAction.exception_1'));                
                }
            }
        }

        //let us get params mergin passed as parameters with those coming from configuration
        if (!class_exists($entity_class_or_definition)) { //it must be a definition
            if (!isset($param_definitions[$entity_class_or_definition])) {
                throw new \Exception($this->trans('controller.renderAction.exception_2'));                
            }                
            $params = array_merge($param_definitions['default'], $param_definitions[$entity_class_or_definition], $params);
        }
        else { //it is an entity       
            $params = array_merge($param_definitions['default'], $params);
            $params['entity_class'] = $entity_class_or_definition;
        }
        $params['find_by'] = $find_by;
        $params['entity_class_or_definition'] = $entity_class_or_definition;
        //here we must have a $params var with all possible entries
        //find the corresponding form if not set
        if (is_null($params['form_type_class'])) { 
            $params['form_type_class'] = $this->guessFormTypeClass($params['entity_class']);
            //TODO
            //if (!class_exists($params['form_type_class'])) {
                //call console command create class
            //}
        }
        //now find the template to render
        if (is_null($render_template)) { //let us guess the template from the $entity_class            
            if (is_null($params['render_template'])) { //let us guess it from 
                $parts = explode ( '\\' , $params['entity_class'] );        
                $params['render_template'] = $parts[0].$parts[1].':Ipe/'.$parts[count($parts)-1].':default.html.twig';
            }//else it must be defined on the definition used
        }
        else { //overwrite the one defined on params
            $params['render_template'] = $render_template;
        } 
        
        
        if ($preloaded_result) {
            $results = $preloaded_result;
        }
        else {
            $rep = $this->container->get('doctrine')->getRepository($params['entity_class']);        
            $order_by = ($params['is_collection'])? array($params['collection_ipe_position_field'] => 'ASC') : array();
            $results = $rep->findBy($params['find_by'], $order_by);                 
            if (!$results) {  
                if ($create_if_not_found) 
                {
                    list($number_of_entities, $locale, $column_formatters) = $this->getFakeDefaults($params);                    
                    $rep->fake($locale, $number_of_entities, $column_formatters, $params['faker_custom_modifiers'], $params['faker_generate_id'] );        
                    $results = $rep->findBy($params['find_by'], $order_by);                 
                }
                else {                    
                    throw new \Exception($this->trans('controller.not_found_exception', array('%entity_class%' => $params['entity_class'] ,'%find_by%' => (( !is_null($params['find_by'])  && is_array($params['find_by']) ) ? implode(' -- ', $params['find_by']) : 'null'))));
                }
            }        
        }
        
        $this->render_vars['results'] = $results;
        $this->render_vars['data_ipe_hash'] = md5(serialize(array($params['find_by'], $params['entity_class'])));        
        $this->render_vars['params'] = $params;

        $session = $this->container->get('request')->getSession();        
        $session->set('ipe_' . $this->render_vars['data_ipe_hash'], $this->render_vars['params']);        

        if ($render_with_container) {            
            return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults( __FUNCTION__), $this->render_vars);
        }
        else {
            return $this->container->get('templating')->renderResponse($params['render_template'], $this->render_vars);        
        }
    }

    public function editIndexAction($ipe_hash)
    {        
        //let us check ipe_locale session var (may be changed afterwards) is set
        $ipe_locale = $ipe_locale = $this->getIpeLocale();
        $params = $this->container->get('request')->getSession()->get('ipe_' . $ipe_hash);
        if ($params['is_collection']) {
            return $this->collectionListAction($ipe_hash);
        }
        else {
            return $this->editAction($ipe_hash);
        }        
    }

    public function editAction($ipe_hash, $action_on_success = null, $id = null)
    {
        $request = $this->container->get('request');
        $params = $request->getSession()->get('ipe_' . $ipe_hash);        
        //the entry MUST alredy exist let us get it
        $em = $this->container->get('doctrine')->getManager();        
        $rep = $em->getRepository($params['entity_class']);
        if (is_null($id)) {
            $entity = $rep->findOneBy($params['find_by']);
            if (!$entity) {
                throw new \Exception($this->trans('controller.not_found_exception', array('%entity_class%' => $params['entity_class'] ,'%find_by%' => (( !is_null($params['find_by'])  && is_array($params['find_by']) ) ? implode(' -- ', $params['find_by']) : 'null'))));
            }
        }
        else {
            $entity = $rep->find($id);    
            if (!$entity) {
                throw new \Exception($this->trans('controller.not_found_exception', array('%entity_class%' => $params['entity_class'] ,'%find_by%' => $id)));
            }
        }
        //die(get_class($entity));
        $form = $this->container->get('form.factory')->create(new $params['form_type_class'](), $entity);
        
        //die(get_class($form));

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($entity);
                //$uploadableManager = $this->container->get('stof_doctrine_extensions.uploadable.manager');

                // Here, "getMyFile" returns the "UploadedFile" instance that the form bound in your $myFile property
                //$uploadableManager->markEntityToUpload($entity, $entity->getPath());
                try{                    
                    $em->flush();
                }
                catch (\Exception $e)
                {

                }
                
                // return new Response(
                //     get_class($uploadableManager)."\n"
                //     .get_class($entity->getPath())."\n"
                //     .var_export($_FILES)
                //     );
                $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.editAction.entry_saved'), 'close' => true, 'use_raw' => true);
                $this->render_vars['reload_content'] = true;
                if ($action_on_success == 'close') {                    
                    $this->render_vars['data_ipe_hash'] = $ipe_hash;
                    return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults('_closeDialog'), $this->render_vars);                
                }
                if ($action_on_success == 'list') {                
                    return $this->collectionListAction($ipe_hash, true);
                }
            }
            else {
                $this->render_vars['flashes'][] = array('type' => 'success', 'error' => $this->trans('controller.editAction.form_errors'), 'close' => true, 'use_raw' => true);
            }
        } 
        if (!isset($this->render_vars['reload_content'])) {
            $this->render_vars['reload_content'] = false;
        }
        $this->render_vars['entity'] = $entity;
        $this->render_vars['form'] = $form->createView();
        $this->render_vars['data_ipe_hash'] = $ipe_hash;
        $this->render_vars['params'] = $params;
        //return new Response('edit'.$ipe_hash);
        return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults( __FUNCTION__), $this->render_vars);            
    }

    public function collectionListAction($ipe_hash, $reload_content = false)
    {
        $params = $this->container->get('request')->getSession()->get('ipe_' . $ipe_hash);        
        $rep = $this->container->get('doctrine')->getRepository($params['entity_class']);        
        $this->render_vars['has__to_string_method'] = (method_exists(new $params['entity_class'](), '__toString'))? true : false;
        if (!$this->render_vars['has__to_string_method']) {
            $this->render_vars['flashes'][] = array('type' => 'warning', 'message' => $this->trans('controller.collectionListAction.to_string_not_defined', array('%entity_class%' => $params['entity_class'])), 'close' => true, 'use_raw' => false);
        }        
        $this->render_vars['inflected_position_field'] = Inflector::classify($params['collection_ipe_position_field']);
        $this->render_vars['reload_content'] = $reload_content;
        $this->render_vars['results'] = $rep->findBy($params['find_by'], array($params['collection_ipe_position_field'] => 'ASC'));
        $this->render_vars['data_ipe_hash'] = $ipe_hash;
        $this->render_vars['params'] = $params;
        //$this->render_vars['advanced_flashes'][] = array('type' => 'error', 'heading' => 'header', 'message' => 'my first error', 'close' => true, 'use_raw' => false);        
        
        return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults( __FUNCTION__) , $this->render_vars);
    }

    public function collectionDeleteItemAction($ipe_hash, $id)
    {        
        $params = $this->container->get('request')->getSession()->get('ipe_' . $ipe_hash);
        $em = $this->container->get('doctrine')->getManager();            
        $entity = $em->getRepository($params['entity_class'])->find($id);
        
        if (!$entity) {            
            throw new \Exception($this->trans('controller.not_found_exception', array('%entity_class%' => $params['entity_class'] ,'%find_by%' => 'id='.$id )));
        }
        $em->remove($entity);
        $em->flush();
        $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.collectionDeleteItemAction.entry_deleted'), 'close' => true, 'use_raw' => true);        
        
        //return new Response('collection '. $ipe_hash. ' ' . $this->getTemplateNameByDefaults( __FUNCTION__));
        return $this->collectionListAction($ipe_hash, true);
    }

    public function collectionSortAction($ipe_hash, $id, $position)
    {
        $params = $this->container->get('request')->getSession()->get('ipe_' . $ipe_hash);        
        $em = $this->container->get('doctrine')->getManager();        
        $entity = $em->getRepository($params['entity_class'])->find($id);
        if (!$entity) {
            throw new \Exception($this->trans('controller.not_found_exception', array('%entity_class%' => $params['entity_class'] ,'%find_by%' => 'id='.$id )));
        }
        $set_position_string = 'set'.Inflector::classify($params['collection_ipe_position_field']);
        $entity->$set_position_string($position);
        $em->persist($entity);
        $em->flush();
        $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.collectionSortAction.list_reordered'), 'close' => true, 'use_raw' => true);        
        
        //return new Response($ipe_hash. '--' . $id. '--' . $position);
        return $this->collectionListAction($ipe_hash, true);
    }

    public function collectionAddItemAction($ipe_hash, $position)
    {        
        $params = $this->container->get('request')->getSession()->get('ipe_' . $ipe_hash);                        
        list($number_of_entities, $locale, $column_formatters) = $this->getFakeDefaults($params);                    
        $column_formatters[$params['collection_ipe_position_field']] = $position;
        $rep = $this->container->get('doctrine')->getRepository($params['entity_class']);
        $rep->fake($locale, 1, $column_formatters, $params['faker_custom_modifiers'], $params['faker_generate_id'] );        

        $this->render_vars['flashes'][] = array('type' => 'success', 'message' => $this->trans('controller.collectionAddItemAction.item_added'), 'close' => true, 'use_raw' => true);        
        
        //return new Response($ipe_hash. '--' . $id. '--' . $position);
        return $this->collectionListAction($ipe_hash, true);
    }

    public function setIpeLocaleAction($locale = null)
    {
        return new Response('Ipe locale changed to '. $this->setIpeLocale($locale));
    }

    public function navbarAction($template = null)
    {
        if (is_null($template))
        {
            $template = $this->getTemplateNameByDefaults( __FUNCTION__)            
        }        
        
        return $this->container->get('templating')->renderResponse($template , $this->render_vars);
    }

    //-------------------------------------------------
    // now private shared functions
    //-------------------------------------------------
    private function setIpeLocale($locale = null)
    {
        if (!$locale) {
            $locale = $this->container->get('request')->getLocale();            
        }
        $this->container->get('request')->getSession()->set('ipe_locale', $locale);
        return $locale;
    }

    private function getIpeLocale($locale = null)
    {
        $ipe_locale = $this->container->get('request')->getSession()->get('ipe_locale');        
        if (!$ipe_locale) {
            $ipe_locale = $this->setIpeLocale();
        }
        return $ipe_locale
    }

    private function getTemplateNameByDefaults($action_function_name, $template_format = 'html')
    {
        $this->render_vars['action_name'] = str_replace('Action', '', $action_function_name);
        return $this->render_vars['bundle_name'] . ':InPageEdit:' . $this->render_vars['action_name'] . '.'.$template_format.'.twig';
    }

    private function trans($translatable, $params = array())
    {
        $ipe_locale = $this->getIpeLocale();
        return $this->container->get('translator')->trans($translatable, $params, $this->render_vars['ipe_message_catalog'], $ipe_locale);
    }   

    private function forward($controller, array $path = array(), array $query = array())
    {
        $path['_controller'] = $controller;
        $subRequest = $this->container->get('request')->duplicate($query, null, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    } 

    private function guessFormTypeClass($entity_class)
    {
        return  str_replace('\\Entity\\', '\\Form\\', $entity_class).'Type';
    }

    private function getFakeDefaults($params)
    {
        $number_of_entities = (!$params['is_collection'])? 1 : $params['number_of_entities_to_fake_if_collection'];                 
        $locale = ($params['faker_locale'])? $params['faker_locale'] : $this->container->get('request')->getLocale();
        $column_formatters = array_merge($params['faker_custom_column_formatters'], $params['find_by']);
        if ($params['is_collection']) {
            $column_formatters[$params['collection_ipe_position_field']] = null;
        }
        return array($number_of_entities, $locale, $column_formatters);
    }

}

