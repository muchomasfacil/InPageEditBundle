<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Contents;

use
    Symfony\Component\Form\AbstractType
    ,Symfony\Component\Form\FormBuilder
    ;

class AdvancedImageType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('img_url', 'text', array(
              'attr' => array('data-mmf-fm'=> 'single_image')
            ))
            ->add('label', 'text')
            ->add('content', 'textarea', array('attr' => array('data-mmf-ie-ckeditor'=> 'default')))
            ->add('link', 'text')
        ;
    }

    public function getName()
    {
        return 'advanced_image';
    }
}
