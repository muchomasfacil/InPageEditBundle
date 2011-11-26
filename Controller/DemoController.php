<?php

namespace MuchoMasFacil\InPageEditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DemoController extends Controller
{

    public function indexAction()
    {    
        $entity_em = $this->getDoctrine()->getEntityManagerForClass('MuchoMasFacil\InPageEditBundle\Entity\Foo'); 

        $repository = $entity_em->getRepository('MuchoMasFacilInPageEditBundle:Foo');

        //$q = $entity_em->createQuery('delete from MuchoMasFacilInPageEditBundle:Foo');
        //$numDeleted = $q->execute();
        $foos =array();
        $foos = $repository->findAll();
        if (count($foos)<=0) {
            for ($a=0; $a < 6; $a++) {
                $foo = new \MuchoMasFacil\InPageEditBundle\Entity\Foo();
                $foo->setText('cadena '. $a);
                $entity_em->persist($foo);
            }
            $entity_em->flush();
            $foos = $repository->findAll();
        }
        
        $contents = array();

        $em = $this->getDoctrine()->getEntityManagerForClass('MuchoMasFacil\InPageEditBundle\Entity\Content'); 

        $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->setContentDefinitions($this->container->getParameter('mucho_mas_facil_in_page_edit.content_definitions'));

        //removeAndCreate //findOrCreateIfNotExist

        $handler = 'one-level-menu-example';
        //$contents[$handler] = $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->removeAndCreate($handler, 'one_level_menu', true);
        
        $handler = 'plain-text-example';
        $contents[$handler] = $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->findOrCreateIfNotExist($handler, 'plain_text');

        $handler = 'plain-text-collection-example';
        $contents[$handler] = $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->removeAndCreate($handler, 'plain_text', true);

        $handler = 'rich-text-example';
        //$contents[$handler] = $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->findOrCreateIfNotExist($handler, 'rich_text');

        $handler = 'custom-rich-text-collection-example';
        //$contents[$handler] = $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->findOrCreateIfNotExist($handler, 'custom_rich_text', true, 5);

        $handler = 'rich-text-header-and-custom-rich-text-example';
        //$contents[$handler] = $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->findOrCreateIfNotExist($handler, 'rich_text_header_and_custom_rich_text');

        $handler = 'image-example';
        //$contents[$handler] = $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->findOrCreateIfNotExist($handler, 'image');

        $handler = 'advanced-image-example';
        //$contents[$handler] = $em->getRepository('MuchoMasFacilInPageEditBundle:Content')->findOrCreateIfNotExist($handler, 'advanced_image', true, 3);

        return $this->render('MuchoMasFacilInPageEditBundle:Demo:index.html.twig', array('contents' => $contents, 'foos' => $foos));
    }


}
