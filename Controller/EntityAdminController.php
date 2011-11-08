<?php

namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;

use MuchoMasFacil\InPageEditBundle\Util\UrlSafeEncoder;

class EntityAdminController extends ContainerAware
{

    private $render_vars = array();

    function __construct()
    {
        $this->render_vars['bundle_name'] = 'MuchoMasFacilInPageEditBundle';
        $this->render_vars['controller_name'] = str_replace('Controller', '', str_replace(__NAMESPACE__.'\\', '', __CLASS__));
    }

    private function getTemplateNameByDefaults($action_name, $template_format = 'html')
    {
      $this->render_vars['action_name'] = str_replace('Action', '', $action_name);
      return $this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':' . $this->render_vars['action_name'] . '.'.$template_format.'.twig';
    }

    private function trans($translatable, $params = array())
    {
      return $this->container->get('translator')->trans($translatable, $params, strtolower($this->render_vars['bundle_name']));
    }

    private function extendedFind($entity_class, $uids)
    {
        $em = $this->getEntityManagerForEntity($entity_class);
        $content = $em->getRepository($entity_class)->findOneBy($uids);

        return $content;
    }

    private function getEntityManagerForEntity($entity_class = '')
    {
        $entity_custom_params = $this->container->getParameter('mucho_mas_facil_in_page_edit.entity_custom_params');
        $orm = (isset($entity_custom_params[$entity_class]['orm']))? $entity_custom_params[$entity_class]['orm'] : $entity_custom_params['default']['orm'];        
        return $this->container->get('doctrine')->getEntityManager($orm);
    }

    private function getAllowedRolesForEntity($entity_class = '')
    {
        $entity_custom_params = $this->container->getParameter('mucho_mas_facil_in_page_edit.entity_custom_params');        
        $allowed_roles = (isset($entity_custom_params[$entity_class]['editor_roles']))? $entity_custom_params[$entity_class]['editor_roles'] : $entity_custom_params['default']['editor_roles'];
        return explode(',', preg_replace('/\s*/m', '', $allowed_roles ));
    }
    
    private function rmdir_recursive($filepath)
    {
        if (is_dir($filepath) && !is_link($filepath)) {
            if ($dh = opendir($filepath)) {
                while (($sf = readdir($dh)) !== false) {
                    if ($sf == '.' || $sf == '..') {
                        continue;
                    }
                    if (!$this->rmdir_recursive($filepath.'/'.$sf)) {
                        //throw new Exception($filepath.'/'.$sf.' could not be deleted.');
                        return false;
                    }
                }
                closedir($dh);
            }
            return @rmdir($filepath);
        }
        return @unlink($filepath);
    }
    
    private function getFormTypeClass($entity_class)
    {
        $type_class = str_replace('\\Entity\\', '\\Form\\', $entity_class).'Type';
        $entity_custom_params = $this->container->getParameter('mucho_mas_facil_in_page_edit.entity_custom_params');
        if ((isset($entity_custom_params[$entity_class]['form_template'])) && (class_exists($entity_custom_params[$entity_class]['form_template']))) {
            $type_class = $entity_custom_params[$entity_class]['form_template'];
        }
        return $type_class;
    }

    private function guessMmfFileManagerPostfix($form_name = '', $uids_as_string = '', $input_id = '')
    {
        $mmf_fm_path_postfix = '';
        if ($form_name) {
            $mmf_fm_path_postfix .= $form_name . '/';
        }
        if ($uids_as_string) {
            $mmf_fm_path_postfix .= $uids_as_string . '/';
        }
        if ($input_id) {
            $mmf_fm_path_postfix .= $input_id . '/';
        }
        return $this->normalizePath($mmf_fm_path_postfix);
    }
    
    private function getUidsAsString($uids)
    {
        return implode('__', $uids);
    }

//------------------------------------------------------------------------------
//actions for object admin
//------------------------------------------------------------------------------

    public function renderAction($url_safe_encoded_params) //this one has an associated route mmf_ie_entity_render
    {
        $url_safe_encoder = new UrlSafeEncoder();
        $params = $url_safe_encoder->decode($url_safe_encoded_params);
        $object = $this->extendedFind($params['entity_class'], $params['uids']);

        return $this->renderObjectAction($object, $params['render_template']);
    }

    public function renderObjectAction($object, $render_template = null)
    {
        if (!$render_template) {
            $parts = explode('\\', get_class($object));
            //we suppose the template is {bundlename}:Render:{class_name}
            $render_template = $parts[0] . $parts[1] . ':Render:' . $parts[count($parts) - 1] . '.html.twig';
        }

        $this->render_vars['object'] = $object;

        return $this->container->get('templating')->renderResponse($render_template, $this->render_vars);
    }

    public function renderObjectWithContainerAction($object, $render_template = null,  $container_html_tag = 'div', $container_html_attributes = '')
    {
        //$session = $this->container->get('request')->getSession();
        $em = $this->getEntityManagerForEntity(get_class($object));
        $metadata = $em->getMetadataFactory()->getMetadataFor(get_class($object));
        $uids = $metadata->getIdentifierValues($object);
        $container_id = $container_html_tag . '__' . implode('__', $uids);
        if (!is_null($this->container->get('security.context')->getToken())) {
            if (true === $this->container->get('security.context')->isGranted($this->getAllowedRolesForEntity(get_class($object)))) {
                $params = array(
                    'render_template' => $render_template
                    ,'entity_class' => get_class($object)
                    ,'uids' => $uids
                    ,'container_id' => $container_id
                );
                $url_safe_encoder = new UrlSafeEncoder();
                $container_html_attributes .= ' data-mmf-ie-edit-url="'.$this->container->get('router')->generate('mmf_ie_edit', array('url_safe_encoded_params' => $url_safe_encoder->encode($params))).'"';
            }
        }
        $this->render_vars['object'] = $object;
        $this->render_vars['render_template'] = $render_template;
        $this->render_vars['container_html_tag'] = $container_html_tag;
        $this->render_vars['container_html_attributes'] = $container_html_attributes;
        $this->render_vars['container_id'] = $container_id;

        return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults(__FUNCTION__), $this->render_vars);
    }

    public function editAction($url_safe_encoded_params, $action_if_success = false)
    {
        $url_safe_encoder = new UrlSafeEncoder();
        $params = $url_safe_encoder->decode($url_safe_encoded_params);

        $entity_class = $params['entity_class'];
        $uids = $params['uids'];
        $render_template = $params['render_template'];
        $container_id = $params['container_id'];

        $object = $this->extendedFind($entity_class, $uids);

        if (false === $this->container->get('security.context')->isGranted($this->getAllowedRolesForEntity(get_class($object)))) {
            throw new AccessDeniedException();
        }


        //$logger = $this->get('logger')->info('hola' . get_class($single_content));
        $type_class = $this->getFormTypeClass($entity_class);

        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new $type_class(), $object);
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $em = $this->getEntityManagerForEntity($entity_class);
                $em->persist($object);
                $em->flush();
                $flash_messages[] = array('highlight' => $this->trans('The entry was SAVED successfully'));
                $reload_container = true;
            }
            else{
                $flash_messages[] = array('error' => $this->trans('Correct form data'));
                $action_if_success = false;
            }
        }

        //if (isset($flash_messages)) $this->get('session')->setFlash('flash_messages', $flash_messages);
        if (isset($flash_messages)) $this->render_vars['flash_messages'] = $flash_messages;
        $this->render_vars['form'] = $form->createView();
        $this->render_vars['form_name'] = $form->getName();

        $this->render_vars['url_safe_encoded_params'] = $url_safe_encoded_params;
        $this->render_vars['container_id'] = $container_id;

        $this->render_vars['uids'] = $uids;
        $this->render_vars['uids_as_string'] = $this->getUidsAsString($uids);

        $this->render_vars['action_if_success'] = $action_if_success;
        $this->render_vars['reload_container'] = (isset($reload_container))? $reload_container: false;
        $this->render_vars['object'] = $object;

        $this->render_vars['render_template'] = $render_template;
        return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults(__FUNCTION__, 'xml'), $this->render_vars);
    }

    public function deleteAction($url_safe_encoded_params, $action_if_success = false)
    {
        $url_safe_encoder = new UrlSafeEncoder();
        $params = $url_safe_encoder->decode($url_safe_encoded_params);

        $entity_class = $params['entity_class'];

        $uids = $params['uids'];
        $render_template = $params['render_template'];
        $container_id = $params['container_id'];

        $object = $this->extendedFind($entity_class, $uids);

        if (false === $this->container->get('security.context')->isGranted($this->getAllowedRolesForEntity(get_class($object)))) {
            throw new AccessDeniedException();
        }

        $em = $this->getEntityManagerForEntity($entity_class);

        if ($object) {
            $em->remove($object);
            $em->flush();
        }

        //now remove associated mmf_fm dir if exists
        $form_class = $this->getFormTypeClass($entity_class);
        $temp_form = new $form_class();
        $form_name = $temp_form->getName();
        $full_target_path = $_SERVER['DOCUMENT_ROOT']. '/uploads'. $this->guessMmfFileManagerPostfix($form_name, $this->getUidsAsString($uids));
        $this->rmdir_recursive($full_target_path); 
    
        $flash_messages[] = array('highlight' => $this->trans('The entry was DELETED successfully'));

        if (isset($flash_messages)) $this->render_vars['flash_messages'] = $flash_messages;
        $this->render_vars['url_safe_encoded_params'] = $url_safe_encoded_params;
        $this->render_vars['action_if_success'] = $action_if_success;
        $this->render_vars['container_id'] = $container_id;

        $this->render_vars['uids'] = $uids;

        return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults(__FUNCTION__, 'xml'), $this->render_vars);
    }

    private function normalizePath ($path)
    {
        //backslash to slash
        $path = str_replace('\\', '/', $path);

        //remove multiple slashes
        $path = str_replace(array('////', '///', '//'), '/', $path);

        if (strlen($path)<=0) $path='/';

        // if there is no an initial slash, add it
        if( $path[0] != '/') $path = '/' . $path;

        // if there is no a final slash, add it
        if( $path[strlen($path)-1] != '/') $path .= '/';

        return $path;
    }

    public function ckeditorConfigAction($ckeditor_option = 'default', $form_name = '', $uids_as_string = '', $input_id = '')
    {
        $ckeditor_options = $this->container->getParameter('mucho_mas_facil_in_page_edit.ckeditor_options');
        if (!isset($ckeditor_options[$ckeditor_option])) {
            $ckeditor_option = 'default';
        }
        $ckeditor_config = $ckeditor_options[$ckeditor_option];

        $ckeditor_guessed_config = array();
        if(class_exists('MuchoMasFacil\FileManagerBundle\Util\CustomUrlSafeEncoder')) {
            $mmf_fm_path_postfix = $this->guessMmfFileManagerPostfix($form_name, $uids_as_string, $input_id);
            $url_safe_encoder = new \MuchoMasFacil\FileManagerBundle\Util\CustomUrlSafeEncoder();
            $params_to_encode = array('upload_path_after_document_root'=> '/uploads'. $mmf_fm_path_postfix .'files/', 'load_options' => 'collection_any_file');
            $ckeditor_guessed_config['filebrowserBrowseUrl'] = $this->container->get('router')->generate('mmf_fm_with_layout', array('url_safe_encoded_params' => $url_safe_encoder->encode($params_to_encode)));

            $params_to_encode = array('upload_path_after_document_root'=> '/uploads'. $mmf_fm_path_postfix.'images/', 'load_options' => 'collection_image');
            $ckeditor_guessed_config['filebrowserImageBrowseUrl'] = $this->container->get('router')->generate('mmf_fm_with_layout', array('url_safe_encoded_params' => $url_safe_encoder->encode($params_to_encode)));

            $params_to_encode = array('upload_path_after_document_root'=> '/uploads'. $mmf_fm_path_postfix .'swf/', 'load_options' => 'collection_swf');
            $ckeditor_guessed_config['filebrowserFlashBrowseUrl'] = $this->container->get('router')->generate('mmf_fm_with_layout', array('url_safe_encoded_params' => $url_safe_encoder->encode($params_to_encode)));

            //config.filebrowserUploadUrl = '';
            //config.filebrowserImageUploadUrl = '';
            //config.filebrowserFlashUploadUrl = '';
        }

        $this->render_vars['ckeditor_guessed_config'] = $ckeditor_guessed_config;
        $this->render_vars['ckeditor_config'] = $ckeditor_config;
        return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults(__FUNCTION__, 'js'), $this->render_vars);
    }

    public function mmfFileManagerBridgeAction($mmf_fm_option = 'default', $form_name = '', $uids_as_string = '', $input_id = '')
    {
        $url_safe_encoder = new \MuchoMasFacil\FileManagerBundle\Util\CustomUrlSafeEncoder();

        $mmf_fm_path_postfix = $this->guessMmfFileManagerPostfix($form_name, $uids_as_string, $input_id);

        $file_manager_params = array(
            'upload_path_after_document_root' => '/uploads' . $mmf_fm_path_postfix
            , 'on_select_callback_function' => //will receive three params: file_name, upload_path_after_root_dir, input_container
                "
                    window.opener.$('#".$input_id."').val(input_value);
                    window.close();
                "
            , 'load_options' => $mmf_fm_option
            );

        $route = $this->container->get('router')->generate('mmf_fm_with_layout', array('url_safe_encoded_params' => $url_safe_encoder->encode($file_manager_params)));
        return  new RedirectResponse($route);

    }

}
