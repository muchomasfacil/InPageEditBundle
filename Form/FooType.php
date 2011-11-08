<?php

namespace MuchoMasFacil\InPageEditBundle\Form;

use
    Symfony\Component\Form\AbstractType
    ,Symfony\Component\Form\FormBuilder
    ;

class FooType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('text', 'text')
            ->add('multi_line_text', 'textarea', array('attr' => array('data-mmf-ie-ckeditor'=> 'custom')))
            ->add('file_list', 'text',  array('attr' => array('data-mmf-fm'=> 'collection_pdf')))
            //->add('file', 'file')
        ;
    }

    public function getName()
    {
        return 'foo';
    }
}
