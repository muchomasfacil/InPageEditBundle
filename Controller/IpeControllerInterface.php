<?php

namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;

interface IpeControllerInterface extends ContainerAware
{
    public function ajaxRenderAction($ipe_hash)

    //no associated route. Will always be called from twig templates
    public function renderAction($ipe_definition, $find_by, $render_template = null, $params = array(), $preload = array())
    /*($find_by = null,
     $entity_class_or_definition = null,
     $preloaded_result = null,
     $render_template = null,
     $create_if_not_found = false,
     $render_with_container = true,
     $params = array())
*/
    public function editIndexAction($ipe_hash)
}