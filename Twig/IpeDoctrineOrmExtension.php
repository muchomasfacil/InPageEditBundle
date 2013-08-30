<?php
namespace MuchoMasFacil\InPageEditBundle\Twig;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Session\Session;

use MuchoMasFacil\InPageEditBundle\Util\IpeTwigExtensionsHelper;

class IpeDoctrineOrmExtension extends IpeExtension
{

    /**
     * Constructor.
     *
     * @param FragmentHandler $handler A FragmentHandler instance
     */
    public function __construct(FragmentHandler $handler, Session $session, Translator $translator, $definitions, $message_catalog)
    {
        parent::__construct($handler, $session, $translator, $definitions, $message_catalog);
    }

    public function getFunctions()
    {
        return array(
            'ipe_render_odme'    => new \Twig_Function_Method($this, 'ipe_render_odme',  array('is_safe' => array('html'))),
            'ipe_render_odgsme'  => new \Twig_Function_Method($this, 'ipe_render_odgsme',  array('is_safe' => array('html'))),
            'ipe_render_odgsme_collection'  => new \Twig_Function_Method($this, 'ipe_render_odgsme_collection',  array('is_safe' => array('html'))),
            'ipe_od_title_tag'   => new \Twig_Function_Method($this, 'ipeTitleTag',  array('is_safe' => array('html'))),
            'ipe_od_meta_tag'    => new \Twig_Function_Method($this, 'ipeMetaTags',  array('is_safe' => array('html'))),
        );
    }    

    public function ipe_render_odme($object, $render_template, $params = array(), $render_with_container = true)
    {
        $ipe_definition = 'orm_doctrine_mapped_entity';
        $definition = $this->definitions[$ipe_definition];
        //let us merge definitions params with call custom params
        $params = array_merge($definition['params'], $params);
        if (is_object($object)) {
            if (method_exists($object, 'getId')) {
                $find_by = array('id' => $object->getId());
            }
            $find_params = array('entity_class' => get_class($object), 'find_by' => $find_by);
        }
        $ipe = array(
                'ipe_definition' => $ipe_definition,
                'find_params' => $find_params,                    
                'render_template' => $render_template,                
                'params' => $params,                
            );

        //now we create our unique ipe_hash            
        $ipe_hash = IpeTwigExtensionsHelper::createHashForObject($ipe);
        //now ipe to session
        $this->session->set('ipe_' . $ipe_hash, $ipe);
        
        $options = array(
            'ipe_hash'  => $ipe_hash,
            'ipe'  => $ipe,
            'render_with_container' => $render_with_container,
            'object' => $object,
            );     

        return $this->renderFragment($this->handler, $this->controller($definition['ipe_controller'].':renderObject', $options));        
    }

    public function ipe_render_odgsme($ipe_handler, $entity_class, $render_template, $params = array(), $render_with_container = true)
    {
        $ipe_definition = 'orm_doctrine_grouped_sorted_mapped_entity_collection';
        $definition = $this->definitions[$ipe_definition];

        $find_params['entity_class'] = $entity_class;
        $find_params['find_by'] = array($definition['params']['collection_ipe_handler_field']=> $ipe_handler);
        $find_params['is_collection'] = false;

        return $this->ipe_render($ipe_definition, $find_params, $render_template, $params = array(), $render_with_container);
    }

    public function ipe_render_odgsme_collection($ipe_handler, $entity_class, $render_template, $params = array(), $render_with_container = true)
    {
        $ipe_definition = 'orm_doctrine_grouped_sorted_mapped_entity_collection';
        $definition = $this->definitions[$ipe_definition];
        
        $find_params['entity_class'] = $entity_class;
        $find_params['find_by'] = array($definition['params']['collection_ipe_handler_field']=> $ipe_handler);
        $find_params['is_collection'] = true;

        return $this->ipe_render($ipe_definition, $find_params, $render_template, $params = array(), $render_with_container);
    }

    public function ipeTitleTag($request, $params = array())
    {   
        return 'falta el título';
        /*$find_params = IpeTwigExtensionsHelper::getTitleFindParams($request->getRequestUri(), $request->getBaseUrl());
        if (!isset($params['form_type_class'])) {
            $params['form_type_class'] = 'MuchoMasFacilInPageEditBundle\\Form\\Type\\TitleTagType';
        }
        if (!isset($params['reload_template'])) {
            //$params['reload_template'] = '';
        }
        $options = array(
            'ipe_definition'  => 'orm_doctrine_grouped_sorted_mapped_entity_collection',
            'find_params'  => $find_params,
            'render_template' => 'MuchoMasFacilInPageEditBundle:ORM:Doctrine/TitleMetaTags/_renderTitleTag.html.twig',
            'params' => $params,
            'render_with_container' => false,
            );

        return $this->renderFragment($this->handler, $this->controller('MuchoMasFacilInPageEditBundle:ORM/Doctrine/GroupedSortedMappedEntityCollection:render', $options));
        */
    }

    public function ipeMetaTags($route_name, $params = array())
    {        
        $find_params = array(
                'entity_class' => 'MuchoMasFacilInPageEditBundle:String',
                'find_by' =>  array('ipe_handler' => $route_name . '__metas'),
                'order_by' => null,
                'is_collection' => true ,
            );
        if (!isset($params['form_type_class'])) {
            $params['form_type_class'] = 'MuchoMasFacilInPageEditBundle\\Form\\Type\\MetaTagType';
        }
        if (!isset($params['reload_template'])) {
            //$params['reload_template'] = '';
        }
        $options = array(
            'ipe_definition'  => 'orm_doctrine_grouped_sorted_mapped_entity_collection',
            'find_params'  => $find_params,
            'render_template' => 'MuchoMasFacilInPageEditBundle:ORM:Doctrine/MetaTags/_render.html.twig',
            'params' => $params,
            'render_with_container' => false,
            );

        return $this->renderFragment($this->handler, $this->controller($definition['ipe_controller'].':render', $options));
    }

    public function getName()
    {
        return 'ipe_doctrine_orm_extension';
    }

}