<?php

namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use MuchoMasFacil\InPageEditBundle\Util\UrlSafeEncoder;
use MuchoMasFacil\InPageEditBundle\Entity\Content as Content;

class ContentAdminController extends ContainerAware
{

    private $render_vars = array();

    function __construct()
    {
        $this->render_vars['bundle_name'] = 'MuchoMasFacilInPageEditBundle';
        $this->render_vars['controller_name'] = str_replace('Controller', '', str_replace(__NAMESPACE__.'\\', '', __CLASS__));
        $this->render_vars['content_class_name'] = 'MuchoMasFacil\InPageEditBundle\Entity\Content';
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

    private function findContentByHandler($handler)
    {
        $em = $this->getEntityManagerForContent();
        $content = $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->find($handler);

        return $content;
    }

    private function getEntityManagerForContent()
    {
        return $this->container->get('doctrine')->getEntityManager($this->container->getParameter('mucho_mas_facil_in_page_edit.content_orm'));
    }

//------------------------------------------------------------------------------
//actions
//------------------------------------------------------------------------------
    //this one has an associated route mmf_ie_content_render
    public function renderContentByHandlerAction($handler, $render_template = null, $url_safe_encoded_custom_params = null) 
    {
        $content = $this->findContentByHandler($handler);
        if (!is_null($url_safe_encoded_custom_params)) {
            $url_safe_encoder = new UrlSafeEncoder();
            $custom_params = $url_safe_encoder->decode($url_safe_encoded_custom_params);
        }
        else {
            $custom_params = null;
        }
        $this->render_vars['content'] = $content;
        $this->render_vars['render_template'] = $render_template;
        $this->render_vars['custom_params'] = $custom_params;
        $forward_action = $this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':' . 'renderContent';
        return $this->container->get('http_kernel')->forward($forward_action, $this->render_vars);
    }

    public function renderContentAction($content, $render_template = null, $custom_params = null)
    {
        if (is_null($render_template)) {
            $render_template = $content->getRenderTemplate();
        }
        $this->render_vars['content'] = $content;
        $this->render_vars['custom_params'] = $custom_params;
        return $this->container->get('templating')->renderResponse($render_template, $this->render_vars);
    }

    public function renderContentWithContainerAction($content, $render_template = null, $container_html_tag = 'div', $container_html_attributes = '', $custom_params = null)
    {
        $container_id = $container_html_tag . '-' . $content->getHandler();
        if (is_null($render_template)) {
            $render_template = $content->getRenderTemplate();
        }
        if (!is_null($this->container->get('security.context')->getToken())) {
            if ((true === $this->container->get('security.context')->isGranted($content->getEditorRolesAsArray())) || (true === $this->container->get('security.context')->isGranted($content->getAdminRolesAsArray()))) {
                $params = array(
                    'render_template'   => $render_template
                    ,'handler'          => $content->getHandler()
                    ,'container_id'     => $container_id
                    ,'custom_params'    => $custom_params
                );
                $url_safe_encoder = new UrlSafeEncoder();
                $container_html_attributes .= ' data-mmf-ie-edit-url="'.$this->container->get('router')->generate('mmf_ie_content_edit', array('url_safe_encoded_params' => $url_safe_encoder->encode($params))).'"';
            }
        }

        $this->render_vars['content'] = $content;
        $this->render_vars['render_template'] = $render_template;
        $this->render_vars['container_html_tag'] = $container_html_tag;
        $this->render_vars['container_html_attributes'] = $container_html_attributes;
        $this->render_vars['container_id'] = $container_id;
        $this->render_vars['custom_params'] = $custom_params;
        return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults(__FUNCTION__), $this->render_vars);
    }

    public function contentEditAction($url_safe_encoded_params)
    {
        $url_safe_encoder = new UrlSafeEncoder();
        $params = $url_safe_encoder->decode($url_safe_encoded_params);
        extract($params);

        $content = $this->findContentByHandler($handler);
        //die(print_r($content, true));

        if (false === $this->container->get('security.context')->isGranted($content->getEditorRolesAsArray())) {
            throw new AccessDeniedException();
        }

        if ($content->getIsCollection()) {
            $action_name = 'collection';
        }
        else {
            $action_name = 'itemEdit';
        }
        $this->render_vars['url_safe_encoded_params'] = $url_safe_encoded_params;
        $forward_action = $this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':' . $action_name;
        return $this->container->get('http_kernel')->forward($forward_action, $this->render_vars);
    }

    public function collectionAction($url_safe_encoded_params,  $reload_container = false)
    {
        $url_safe_encoder = new UrlSafeEncoder();
        $params = $url_safe_encoder->decode($url_safe_encoded_params);
        extract($params);
        $content = $this->findContentByHandler($handler);

        if (false === $this->container->get('security.context')->isGranted($content->getEditorRolesAsArray())) {
            throw new AccessDeniedException();
        }

        $this->render_vars['container_id'] = $container_id;
        $this->render_vars['reload_container'] = $reload_container;
        $this->render_vars['content'] = $content;
        $this->render_vars['render_template'] = $render_template;
        $this->render_vars['url_safe_encoded_params'] = $url_safe_encoded_params;
        $this->render_vars['url_safe_encoded_custom_params'] = $url_safe_encoder->encode($custom_params);
        
        return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults(__FUNCTION__, 'xml'), $this->render_vars);
    }

    public function collectionAddItemAction($url_safe_encoded_params, $content_order)
    {
        $url_safe_encoder = new UrlSafeEncoder();
        $params = $url_safe_encoder->decode($url_safe_encoded_params);
        extract($params);

        $content = $this->findContentByHandler($handler);

        if (false === $this->container->get('security.context')->isGranted($content->getEditorRolesAsArray())) {
            throw new AccessDeniedException();
        }

        try
        {
            $content_content = $content->getContent();
            if ((!is_null($content->getCollectionLength())) && (count($content_content) >= $content->getCollectionLength())) {
                $flash_messages[] = array('error' => $this->trans('Overpassed maximum number of items for this content'). ': '.$content->getCollectionLength());
                $reload_container = false;
            }
            else{
                $class_name = $content->getEntityClass();
                $new_entry = new $class_name();
                if (isset($content_content[$content_order])){
                    $content_to_insert[] = $new_entry->getLoremIpsum();
                    $content_to_insert[] = $content_content[$content_order];
                    array_splice($content_content, $content_order, 1, $content_to_insert);
                }
                else
                {
                    $content_to_insert = $new_entry->getLoremIpsum();
                    $content_content[] =  $content_to_insert;
                }
                $reload_container = true;
                $content->setContent(array_values($content_content));
                $em = $this->getEntityManagerForContent();
                $em->persist($content);
                $em->flush();
                $flash_messages[] = array('highlight' => $this->trans('The new entry was created successfully'));
            }
        }
        catch (Exception $e){
            $flash_messages[] = array('error' => $this->trans('There was a problem creating the new entry'));
            $reload_container = false;
        }

        if (isset($flash_messages)) $this->render_vars['flash_messages'] = $flash_messages;
        $this->render_vars['container_id'] = $container_id;
        $this->render_vars['reload_container'] = $reload_container;
        $this->render_vars['content'] = $content;
        $this->render_vars['url_safe_encoded_custom_params'] = $url_safe_encoder->encode($custom_params);

        $this->render_vars['render_template'] = $render_template;
        $this->render_vars['url_safe_encoded_params'] = $url_safe_encoded_params;

        return $this->container->get('templating')->renderResponse($this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':collection.xml.twig', $this->render_vars);
    }

    public function collectionDeleteItemAction($url_safe_encoded_params, $content_order)
    {
        $url_safe_encoder = new UrlSafeEncoder();
        $params = $url_safe_encoder->decode($url_safe_encoded_params);
        extract($params);

        $content = $this->findContentByHandler($handler);

        if (false === $this->container->get('security.context')->isGranted($content->getEditorRolesAsArray())) {
            throw new AccessDeniedException();
        }

        $content_content = $content->getContent();

        if (isset($content_content[$content_order]))
        {
            if (isset($content_content[$content_order])){
                unset($content_content[$content_order]);
                $content->setContent(array_values($content_content));
                $flash_messages[] = array('highlight' => $this->trans('The entry was deleted successfully'));
                $reload_container = true;
            }
            else{
                $flash_messages[] = array('error' => $this->trans('The entry did not exist. Nothing deleted'));
                $reload_container = false;
            }
        }

        if ($content_order == 'all'){
            $content->setContent(null);
            $flash_messages[] = array('highlight' => $this->trans('All entries were deleted successfully'));
            $reload_container = true;
        }
        $em = $this->getEntityManagerForContent();
        $em->persist($content);
        $em->flush();

        if (isset($flash_messages)) $this->render_vars['flash_messages'] = $flash_messages;
        $this->render_vars['container_id'] = $container_id;
        $this->render_vars['reload_container'] = $reload_container;
        $this->render_vars['content'] = $content;
        $this->render_vars['url_safe_encoded_custom_params'] = $url_safe_encoder->encode($custom_params);

        $this->render_vars['render_template'] = $render_template;
        $this->render_vars['url_safe_encoded_params'] = $url_safe_encoded_params;

        return $this->container->get('templating')->renderResponse($this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':collection.xml.twig', $this->render_vars);
    }

    public function collectionSortAction($url_safe_encoded_params, $reload_container = false)
    {
        $url_safe_encoder = new UrlSafeEncoder();
        $params = $url_safe_encoder->decode($url_safe_encoded_params);
        extract($params);
        //die(print_r($params));
        $content = $this->findContentByHandler($handler);

        if (false === $this->container->get('security.context')->isGranted($content->getEditorRolesAsArray())) {
            throw new AccessDeniedException();
        }

        $content_content = $content->getContent();
        $li_sortable = $this->container->get('request')->get('mmf-ie-li-sortable');
        $new_content = array();
        foreach ($li_sortable as $value){
            $new_content[] =  $content_content[$value];
        }

        $content->setContent(array_values($new_content));
        $em = $this->getEntityManagerForContent();
        $em->persist($content);
        $em->flush();

        $flash_messages[] = array('highlight' => $this->trans('The entry was moved successfully'));

        if (isset($flash_messages)) $this->render_vars['flash_messages'] = $flash_messages;
        $this->render_vars['container_id'] = $container_id;
        $this->render_vars['reload_container'] = $reload_container;
        $this->render_vars['content'] = $content;
        $this->render_vars['url_safe_encoded_custom_params'] = $url_safe_encoder->encode($custom_params);

        $this->render_vars['render_template'] = $render_template;
        $this->render_vars['url_safe_encoded_params'] = $url_safe_encoded_params;

        return $this->container->get('templating')->renderResponse($this->render_vars['bundle_name'] . ':' . $this->render_vars['controller_name'] . ':collection.xml.twig', $this->render_vars);
    }

    public function itemEditAction($url_safe_encoded_params, $content_order = 0, $action_if_success = false)
    {
        $request = $this->container->get('request');
        $url_safe_encoder = new UrlSafeEncoder();
        $params = $url_safe_encoder->decode($url_safe_encoded_params);
        extract($params);

        $content = $this->findContentByHandler($handler);

        if (false === $this->container->get('security.context')->isGranted($content->getEditorRolesAsArray())) {
            throw new AccessDeniedException();
        }

        $entity_class = $content->getEntityClass();
        $content_content = $content->getContent();
        if (!isset($content_content[$content_order]))
        {

            $content_content[$content_order] = new $entity_class();
        }
        $single_content = $content_content[$content_order];
        //$logger = $this->get('logger')->info('hola' . get_class($single_content));
        $type_class = str_replace('\\Entity\\', '\\Form\\', $entity_class).'Type';
        $entity_custom_params = $this->container->getParameter('mucho_mas_facil_in_page_edit.entity_custom_params');
        if ((isset($entity_custom_params[$entity_class]['form_template'])) && (class_exists($entity_custom_params[$entity_class]['form_template']))) {
            $type_class = $entity_custom_params[$entity_class]['form_template'];
        }

        $form = $this->container->get('form.factory')->create(new $type_class(), $single_content);
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $em = $this->getEntityManagerForContent();
                $content_content[$content_order] = $single_content;
                //double flush with null entry to avoid the unitofwork not updating the array
                $content->setContent(null);
                $em->persist($content);
                $em->flush();
                $content->setContent(array_values($content_content));
                $em->persist($content);
                $em->flush();

                $flash_messages[] = array('highlight' => $this->trans('The entry was saved successfully'));
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

        $this->render_vars['form_name'] = 'content';
        $this->render_vars['uids_as_string'] = $handler;

        $this->render_vars['content_order'] = $content_order;
        $this->render_vars['action_if_success'] = $action_if_success;

        $this->render_vars['container_id'] = $container_id;
        $this->render_vars['reload_container'] = (isset($reload_container))? $reload_container: false;
        $this->render_vars['content'] = $content;
        $this->render_vars['url_safe_encoded_custom_params'] = $url_safe_encoder->encode($custom_params);
        
        $this->render_vars['render_template'] = $render_template;
        $this->render_vars['url_safe_encoded_params'] = $url_safe_encoded_params;

        return $this->container->get('templating')->renderResponse($this->getTemplateNameByDefaults(__FUNCTION__, 'xml'), $this->render_vars);

    }


}
