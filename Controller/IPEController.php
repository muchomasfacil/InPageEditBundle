<?php
namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\Common\Util\Inflector;

use MuchoMasFacil\InPageEditBundle\Util\IpeTwigExtensionsHelper;

class IPEController extends ContainerAware
{
    protected $render_vars = array();

    function __construct()
    {
        list($bundle_name, $controller_name) = $this->guessBundleAndControllerName($this);
        list($parent_bundle_name, $parent_controller_name) = $this->guessBundleAndControllerName(__CLASS__);

        $this->render_vars['bundle_name'] = $bundle_name;
        $this->render_vars['controller_name'] = $controller_name;
        $this->render_vars['parent_bundle_name'] = $parent_bundle_name;
        $this->render_vars['parent_controller_name'] = $parent_controller_name;
    }

    public function ipeAction($ipe_hash, $action, Request $request)
    {
        $ipe = IpeTwigExtensionsHelper::getIpe($this->container->get('request')->getSession(), $ipe_hash);
        $definitions = $this->container->getParameter('mucho_mas_facil_in_page_edit.definitions');
        $definition = $definitions[$ipe['ipe_definition']];
        $action = Inflector::camelize($action);

        return $this->forward($definition['ipe_controller'].':'.$action, array(
            'ipe_hash'=> $ipe_hash,
            'ipe'=>$ipe,
            'request' => $request,
            ));
    }

    public function _navbarAction($root_request, $template = null, $locale = null)
    {
        $this->render_vars['available_langs'] = $this->container->getParameter('mucho_mas_facil_in_page_edit.available_langs');
        if (is_null($locale)) {
            $this->render_vars['ipe_locale'] = $this->getIpeLocale();
        }
        else {
            $this->render_vars['ipe_locale'] = $this->setIpeLocale($locale);
        }

        if (is_null($template))
        {
            $template = $this->render_vars['parent_bundle_name'] . ':' . $this->render_vars['parent_controller_name'] . ':_navbar.html.twig';
        }

        $session = $this->container->get('session');
        $request = $this->container->get('request');

        $ipe_handler ='el_que_sea';// IpeTwigExtensionsHelper::getTitleHandler($root_request->getRequestUri(), $root_request->getBaseUrl());

        //some magic to
        $this->render_vars['title_data_ipe_hash'] = 'elquesea';
        $this->render_vars['template'] = $template;

        /*echo '---';
        var_dump($this->render_vars['title_data_ipe_hash']);
        var_dump($session->all());*/

        return $this->container->get('templating')->renderResponse($template , $this->render_vars);
    }

//this should always by called for IPEController:ajaxIpeRenderAction
    public function ajaxRenderAction($ipe_hash, $ipe, Request $request)
    {
        return $this->forward($this->render_vars['bundle_name'] . ':'.$this->render_vars['controller_name'].':render', array(
            'ipe_hash'  => $ipe_hash,
            'ipe'  => $ipe,
            'render_with_container' => false,
            'request' => $request
        ));
    }

    //no associated route. Will always be called from twig templates or from ajaxRenderAction
    public function renderAction($ipe_hash, $ipe, $render_with_container = true)
    {
        if ($render_with_container) {
            $this->getIpeLocale(); //init ipe_locale
        }

        $this->render_vars['data_ipe_hash'] = $ipe_hash;
        $this->render_vars['ipe_definition'] = $ipe['ipe_definition'];
        $this->render_vars['find_params'] = $ipe['find_params'];
        $this->render_vars['render_template'] = $ipe['render_template'];
        $this->render_vars['params'] = $ipe['params'];

        $this->render_vars['render_with_container'] = $render_with_container;
        $this->render_vars['object'] = $this->getObject($ipe['ipe_definition'], $ipe['find_params'], $ipe['params']);

        if ($render_with_container) {
            $final_render_template = $this->render_vars['parent_bundle_name'] . ':' . $this->render_vars['parent_controller_name'] . ':render.html.twig';
        }
        else {
            $final_render_template = $ipe['render_template'];
        }

        return $this->container->get('templating')->renderResponse($final_render_template , $this->render_vars);
    }

    protected function guessBundleAndControllerName($bundle_class_name)
    {
        $reflector = new \ReflectionClass($bundle_class_name);
        $class_name = $reflector->getName();
        // get string left of \Controller\
        $bundle_name = strstr($class_name, '\\Controller\\', true);
        // get string right of \Controller\
        $controller_name = str_replace($bundle_name . '\\Controller\\', '', $class_name);
        // remove final Controller part
        $controller_name = str_replace('Controller', '', $controller_name);
        // join bundle parts to form bundle name
        $bundle_name = str_replace('\\', '', $bundle_name);

        return array($bundle_name, $controller_name);
    }

    protected function guessFormTypeClass($entity_class_name)
    {
        return  str_replace('\\Entity\\', '\\Form\\', $entity_class_name).'Type';
    }

    protected function checkRoles($roles)
    {
        if ((!empty($roles)) && (false === $this->container->get('security.context')->isGranted($roles))) {
            throw new AccessDeniedException();
        }
    }


    protected function removeIpe($ipe_hash)
    {
        $session = $this->container->get('request')->getSession();
        $session->remove('ipe_' . $ipe_hash);
    }

    protected function setIpeLocale($locale = null)
    {
        if (!$locale) {
            $locale = $this->container->get('request')->getLocale();
        }
        $this->container->get('request')->getSession()->set('ipe_locale', $locale);
        return $locale;
    }

    protected function getIpeLocale()
    {
        $ipe_locale = $this->container->get('request')->getSession()->get('ipe_locale');
        if (!$ipe_locale) {
            $ipe_locale = $this->setIpeLocale();
        }
        return $ipe_locale;
    }

    protected function trans($translatable, $params = array())
    {
        $ipe_locale = $this->getIpeLocale();
        return $this->container->get('translator')->trans($translatable, $params, $this->container->getParameter('mucho_mas_facil_in_page_edit.message_catalog'), $ipe_locale);
    }

    protected function forward($controller, array $path = array(), array $query = array())
    {
        $path['_controller'] = $controller;
        $subRequest = $this->container->get('request')->duplicate($query, null, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

}