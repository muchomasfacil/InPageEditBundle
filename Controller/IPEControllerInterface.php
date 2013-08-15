<?php

namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

interface IPEControllerInterface
{
    public function ajaxRenderAction($ipe, $ipe_hash);
    //no associated route. Will always be called from twig templates
    public function renderAction($ipe_definition, $find_params, $render_template = null, $params = array(), $render_with_container);

    public function editAction($ipe_hash, Request $request);

    //this get find_object_params discriminating object_or_find_object_params to, for example, save a doctrine query
    public function getFindParams($ipe_definition, $find_params, $params);

    //this get object discriminating object_or_find_object_params to may be save a for example, doctrine query
    public function getObject($ipe_definition, $find_params, $params);

}