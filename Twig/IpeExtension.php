<?php
namespace MuchoMasFacil\InPageEditBundle\Twig;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Session\Session;

use MuchoMasFacil\InPageEditBundle\Util\IpeTwigExtensionsHelper;

class IpeExtension extends \Twig_Extension
{
    /**
     *
     * @var  \Symfony\Component\DependencyInjection\Container
     */
    protected $handler;

    protected $session;

    protected $translator;

    protected $definitions;

    protected $message_catalog;
    /**
     * Constructor.
     *
     * @param FragmentHandler $handler A FragmentHandler instance
     */
    public function __construct(FragmentHandler $handler, Session $session, Translator $translator, $definitions, $message_catalog)
    {
        $this->handler = $handler;
        $this->session = $session;
        $this->translator = $translator;
        $this->definitions = $definitions;
        $this->message_catalog = $message_catalog;
    }

    public function getFunctions()
    {
        return array(
            'ipe_render' => new \Twig_Function_Method($this, 'ipe_render',  array('is_safe' => array('html'))),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ipe_trans', array($this, 'ipe_trans')),
        );
    }

    public function ipe_trans($translatable, $params = array(), $message_catalog = null, $ipe_locale = null)
    {
        if (empty($message_catalog)) {
            $message_catalog = $this->message_catalog;
        }
        if (empty($ipe_locale)) {
            $ipe_locale = $this->session->get('ipe_locale');
        }
        return $this->translator->trans($translatable, $params, $message_catalog, $ipe_locale);
    }

    public function ipe_render($ipe_definition, $find_params, $render_template, $params = array(), $render_with_container = true)
    {
        //create var to store in session
        $ipe = IpeTwigExtensionsHelper::createIpe($ipe_definition, $this->definitions, $find_params, $render_template, $params);
        //now we create our unique ipe_hash BASED on that var
        $ipe_hash = IpeTwigExtensionsHelper::createHashForObject($ipe);
        //now ipe to session
        $this->session->set('ipe_' . $ipe_hash, $ipe);
        $options = array(
            'ipe_hash'  => $ipe_hash,
            'ipe'  => $ipe,
            'render_with_container' => $render_with_container,
            );

        return IpeTwigExtensionsHelper::renderFragment($this->handler, IpeTwigExtensionsHelper::controller($definition['ipe_controller'].':render', $options));
    }

    public function getName()
    {
        return 'ipe_extension';
    }

}