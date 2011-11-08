<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Contents;

use
    Symfony\Component\Form\AbstractType
    ,Symfony\Component\Form\FormBuilder
    ;

class RichTextHeaderAndCustomRichTextType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('header', 'textarea', array('attr' => array('data-mmf-ie-ckeditor'=> 'default')))
            ->add('content', 'textarea', array('attr' => array('data-mmf-ie-ckeditor'=> 'custom')))
            ;
    }

    public function getName()
    {
        return 'rich_text_header_and_custom_rich_text';
    }
}

