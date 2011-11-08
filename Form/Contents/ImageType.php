<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Contents;

use
    Symfony\Component\Form\AbstractType
    ,Symfony\Component\Form\FormBuilder
    ;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('file_list', 'text', array(
              'attr' => array('data-mmf-fm'=> 'collection_image')
            ))
        ;
    }

    public function getName()
    {
        return 'image';
    }
}
