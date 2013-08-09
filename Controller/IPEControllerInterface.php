<?php

namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

interface IPEControllerInterface
{
    public function ajaxRenderAction($ipe, $ipe_hash);
    //no associated route. Will always be called from twig templates
    public function renderAction($ipe_definition, $object_or_object_finder, $render_template = null, $params = array(), $render_with_container);

    public function editAction($ipe_hash, Request $request);

    //this get find_object_params discriminating object_or_find_object_params to may be save a for example, doctrine query
    public function getFindObjectParams($ipe_definition, $object_or_find_object_params, $render_template, $params, $render_with_container);

    //this get object discriminating object_or_find_object_params to may be save a for example, doctrine query
    public function getObject($ipe_definition, $object_or_find_object_params, $render_template, $params, $render_with_container);

    public function isFindObjectParams($object_or_find_object_params)

    public function findObject($find_object_params);
}