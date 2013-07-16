<?php

namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;

interface IPEControllerInterface extends ContainerAware
{
    public function ajaxRenderAction($ipe_hash)

    public function getFindObjectParams($ipe_definition, $object, $render_template, $params, $render_with_container)

    //no associated route. Will always be called from twig templates
    public function renderAction($ipe_definition, $object, $render_template = null, $params = array(), $render_with_container)

    public function editIndexAction($ipe_hash)
}