<?php
namespace MuchoMasFacil\InPageEditBundle\Twig;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Session\Session;

use MuchoMasFacil\InPageEditBundle\Util\IpeTwigExtensionsHelper;

class IpeOrmDoctrineExtension extends IpeExtension
{

    /**
     * Constructor.
     *
     * @param FragmentHandler $handler A FragmentHandler instance
     */
    // public function __construct(SecurityContext $security_context, FragmentHandler $handler, Session $session, Translator $translator, $definitions, $message_catalog)
    // {
    //     parent::__construct($handler, $session, $translator, $definitions, $message_catalog);
    // }

    public function getFunctions()
    {
        return array(
            'ipe_render_odme'    => new \Twig_Function_Method($this, 'ipe_render_odme',  array('is_safe' => array('html'))),
            'ipe_render_odgsme'  => new \Twig_Function_Method($this, 'ipe_render_odgsme',  array('is_safe' => array('html'))),
            'ipe_render_odgsme_collection'  => new \Twig_Function_Method($this, 'ipe_render_odgsme_collection',  array('is_safe' => array('html'))),
            'ipe_title_tag_od'   => new \Twig_Function_Method($this, 'ipe_title_tag_od',  array('is_safe' => array('html'))),
            'ipe_meta_tags_od'    => new \Twig_Function_Method($this, 'ipe_meta_tags_od',  array('is_safe' => array('html'))),
        );
    }

    public function ipe_render_odme($object, $render_template, $params = array(), $render_with_container = true)
    {
        $ipe_definition = 'orm_doctrine_mapped_entity';
        // now create find_params
        if (is_object($object)) {
            if (method_exists($object, 'getId')) {
                $find_by = array('id' => $object->getId());
            }
            $find_params = array('entity_class' => get_class($object), 'find_by' => $find_by);
        }
        //create var to store in session
        $ipe = IpeTwigExtensionsHelper::createIpe($ipe_definition, $this->definitions, $find_params, $render_template, $params = array());
        //now we create our unique ipe_hash
        $ipe_hash = IpeTwigExtensionsHelper::createHashForObject($ipe);
        //now ipe to session
        if ((empty($ipe['params']['editor_roles'])) || ($this->security_context->isGranted($ipe['params']['editor_roles']))) {
            IpeTwigExtensionsHelper::setIpe($this->session, $ipe_hash, $ipe);
        }

        $options = array(
            'ipe_hash'  => $ipe_hash,
            'ipe'  => $ipe,
            'render_with_container' => $render_with_container,
            'object' => $object,
            );
        // we don't use $this->ipe_render so we save another sql query
        return IpeTwigExtensionsHelper::renderFragment($this->handler, IpeTwigExtensionsHelper::controller($this->definitions[$ipe_definition]['ipe_controller'].':renderObject', $options));
    }

    public function ipe_render_odgsme($ipe_handler, $entity_class, $render_template, $params = array(), $render_with_container = true)
    {
        $ipe_definition = 'orm_doctrine_grouped_sorted_mapped_entity_collection';
        $definition = $this->definitions[$ipe_definition];

        $find_params['entity_class'] = $entity_class;
        $find_params['find_by'] = array($definition['params']['collection_ipe_handler_field']=> $ipe_handler);
        $find_params['is_collection'] = false;

        return $this->ipe_render($ipe_definition, $find_params, $render_template, $params , $render_with_container);
    }

    public function ipe_render_odgsme_collection($ipe_handler, $entity_class, $render_template, $params = array(), $render_with_container = true)
    {
        $ipe_definition = 'orm_doctrine_grouped_sorted_mapped_entity_collection';
        $definition = $this->definitions[$ipe_definition];

        $find_params['entity_class'] = $entity_class;
        $find_params['find_by'] = array($definition['params']['collection_ipe_handler_field']=> $ipe_handler);
        $find_params['is_collection'] = true;

        return $this->ipe_render($ipe_definition, $find_params, $render_template, $params, $render_with_container);
    }

    public function ipe_title_tag_od($request, $add_query_params = false, $params = array(), $render_with_container = true)
    {
        $ipe_handler = IpeTwigExtensionsHelper::getHashForRoute($request, $add_query_params).'__title_tag';
        $entity_class = 'MuchoMasFacilInPageEditBundle:GroupedSortedMappedString';
        $render_template = 'MuchoMasFacilInPageEditBundle:ORM:Doctrine/TitleMetaTags/_renderTitleTag.html.twig';
        if (!isset($params['form_type_class'])) {
            $params['form_type_class'] = 'MuchoMasFacil\\InPageEditBundle\\Form\\Type\\TitleTagType';
        }
        if (!isset($params['reload_template'])) {
            $params['reload_template'] = 'MuchoMasFacilInPageEditBundle:ORM:Doctrine/TitleMetaTags/_reloadTitleTag.html.twig';
        }

        return $this->ipe_render_odgsme($ipe_handler, $entity_class, $render_template, $params, $render_with_container);
    }

    public function ipe_meta_tags_od($request, $add_query_params = false, $params = array(), $render_with_container = true)
    {
        $ipe_handler = IpeTwigExtensionsHelper::getHashForRoute($request, $add_query_params).'__meta_tags';
        $entity_class = 'MuchoMasFacilInPageEditBundle:GroupedSortedMappedString';
        $render_template = 'MuchoMasFacilInPageEditBundle:ORM:Doctrine/TitleMetaTags/_renderMetaTags.html.twig';
        if (!isset($params['form_type_class'])) {
            $params['form_type_class'] = 'MuchoMasFacil\\InPageEditBundle\\Form\\Type\\MetaTagType';
        }
        if (!isset($params['reload_template'])) {
            $params['reload_template'] = 'MuchoMasFacilInPageEditBundle:ORM:Doctrine/TitleMetaTags/_reloadMetaTags.html.twig';
        }

        return $this->ipe_render_odgsme_collection($ipe_handler, $entity_class, $render_template, $params, $render_with_container);
    }

    public function getName()
    {
        return 'ipe_orm_doctrine_extension';
    }

}