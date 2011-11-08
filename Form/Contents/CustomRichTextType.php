<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Contents;

use
    Symfony\Component\Form\AbstractType
    ,Symfony\Component\Form\FormBuilder
    ;

class CustomRichTextType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('content', 'textarea', array(
              'attr' => array('data-mmf-ie-ckeditor'=> 'custom')
            ));
    }

    public function getName()
    {
        return 'custom_rich_text';
    }
}

