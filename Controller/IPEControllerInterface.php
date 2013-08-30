<?php

namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

interface IPEControllerInterface
{
    public function ajaxRenderAction($ipe, $ipe_hash, Request $request);
    
    public function editAction($ipe_hash, $ipe, Request $request);

    //no associated route. Will always be called from twig templates
    public function renderAction($ipe_hash, $ipe, $render_with_container);

    //this get object discriminating object_or_find_object_params to may be save a for example, doctrine query
    public function getObject($ipe_definition, $find_params, $params);

}