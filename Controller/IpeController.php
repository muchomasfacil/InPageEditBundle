<?php
namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Common\Util\Inflector;

use MuchoMasFacil\InPageEditBundle\Controller\ControllerInterface;

class IpeController extends ContainerAware //implements ControllerInterface
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

        return $this->forward($definition['ipe_controller'].':'.$action, array('ipe'=>$ipe));
    }

    public function ipeSetLocaleAction($locale = null)
    {
        return new Response('Ipe locale changed to '. $this->setIpeLocale($locale));
    }

    private function guessBundleAndControllerName($class)
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

    protected function getTemplateNameByDefaults($action_function_name, $template_format = 'html')
    {
        $this->render_vars['action_name'] = str_replace('Action', '', $action_function_name);
        return $this->render_vars['bundle_name'] . ':InPageEdit:' . $this->render_vars['action_name'] . '.'.$template_format.'.twig';
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

    protected function guessFormTypeClass($entity_class)
    {
        return  str_replace('\\Entity\\', '\\Form\\', $entity_class).'Type';
    }

}