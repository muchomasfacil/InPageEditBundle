<?php
namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Common\Util\Inflector;

use MuchoMasFacil\InPageEditBundle\Controller\ControllerInterface;

class IPEController extends ContainerAware //implements ControllerInterface
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
        $this->render_vars['ipe_message_catalog'] = 'mmf_ipe';
    }

    public function ipeAction($ipe_hash, $action)
    {
        $ipe = $this->getIpe($ipe_hash);
        $definitions = $this->container->getParameter('mucho_mas_facil_in_page_edit.definitions');
        $definition = $definitions[$ipe['ipe_definition']];
        $action = Inflector::camelize($action);

        return $this->forward($definition['ipe_controller'].':'.$action, array('ipe_hash'=> $ipe_hash, 'ipe'=>$ipe));
    }

    public function ipeSetLocaleAction($locale = null)
    {
        return new Response('Ipe locale changed to '. $this->setIpeLocale($locale));
    }

    //no associated route. Will always be called from twig templates or from ajaxRenderAction
    public function renderAction($ipe_definition, $object, $render_template, $params = array(), $render_with_container = true)
    {
        $definitions = $this->container->getParameter('mucho_mas_facil_in_page_edit.definitions');
        $this->getIpeLocale();
        $definition = $definitions[$ipe_definition];
        $params = array_merge($definition['params'], $params);

        $this->render_vars['ipe_definition'] = $ipe_definition;
        $this->render_vars['object'] = $object;
        $this->render_vars['params'] = $params;
        $this->render_vars['render_with_container'] = $render_with_container;

        if (!$render_with_container) {
            $final_render_template = $render_template;
        }
        else {
            // prepare values for session:
            // first, those from the function params
            $ipe = array(
                'ipe_definition' => $ipe_definition,
                'render_template' => $render_template,
                'params' => $params,
                //this should be specific by definition
                'find_object_params' => $this->getFindObjectParams($ipe_definition, $object, $render_template, $params , $render_with_container)
            );
            // then those that can be overwritten by params
            foreach(array('editor_roles', 'container_html_tag', 'container_html_attributes') as $key) {
                $value = (isset($params[$key]))? $params[$key]: $definition[$key];
                $ipe[$key] = $value;
            }
            $ipe_hash = $this->createDataIpeHash($ipe);
            //now ipe to session
            $this->setIpe($ipe_hash, $ipe);

            // now add render info
            $this->render_vars['editor_roles'] = $ipe['editor_roles'];
            $this->render_vars['container_html_tag'] = $ipe['container_html_tag'];
            $this->render_vars['container_html_attributes'] = $ipe['container_html_attributes'];
            $this->render_vars['render_template'] = $ipe['render_template'];

            $this->render_vars['data_ipe_hash'] = $ipe_hash;
            $this->render_vars['show_data_ipe_hash'] = $this->ipeIsGranted($ipe['editor_roles']);

            $final_render_template = $this->render_vars['parent_bundle_name'] . ':' . $this->render_vars['parent_controller_name'] . ':render.html.twig';
        }

        return $this->container->get('templating')->renderResponse($final_render_template , $this->render_vars);
    }

    protected function guessBundleAndControllerName($class)
    {
        $reflector = new \ReflectionClass($class);
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

    protected function checkRoles($roles)
    {
        if (!$this->ipeIsGranted($roles)) {
            throw new AccessDeniedException();
        }
    }

    protected function ipeIsGranted($roles)
    {
        if ((!is_array($roles)) && (!is_null($roles))) {
            throw new \Exception($this->trans('controller.editor_roles_error'));
        }
        if ((is_array($roles)) && (count($roles) > 0)) {
            if (
                (is_null($this->container->get('security.context')->getToken()))
                || (false === $this->container->get('security.context')->isGranted($roles))
                ) {
                return false;
            }
        }
        return true;
    }

    protected function setIpe($ipe_hash, $ipe)
    {
        $session = $this->container->get('request')->getSession();
        $session->set('ipe_' . $ipe_hash, $ipe);
    }

    protected function getIpe($ipe_hash)
    {
        $session = $this->container->get('request')->getSession();
        $ipe = $session->get('ipe_'.$ipe_hash, null);
        if (is_null($ipe)) {
            throw new \Exception('No ipe entry found for hash: '. $ipe_hash);
        }

        return $ipe;
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
        return $this->container->get('translator')->trans($translatable, $params, $this->render_vars['ipe_message_catalog'], $ipe_locale);
    }

    protected function forward($controller, array $path = array(), array $query = array())
    {
        $path['_controller'] = $controller;
        $subRequest = $this->container->get('request')->duplicate($query, null, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    protected function guessFormTypeClass($entity)
    {
        $entity_class = get_class($entity);
        return  str_replace('\\Entity\\', '\\Form\\', $entity_class).'Type';
    }

}