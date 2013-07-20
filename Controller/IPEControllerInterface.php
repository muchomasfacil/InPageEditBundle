<?php

namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

interface IPEControllerInterface
{
    public function ajaxRenderAction($ipe, $ipe_hash);
    //no associated route. Will always be called from twig templates
    public function renderAction($ipe_definition, $object, $render_template = null, $params = array(), $render_with_container);

    public function editAction($ipe_hash, Request $request);

    public function getFindObjectParams($ipe_definition, $object, $render_template, $params, $render_with_container);

    public function findObject($find_object_params);
}