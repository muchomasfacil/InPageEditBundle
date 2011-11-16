<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Contents;

use
    Symfony\Component\Form\AbstractType
    ,Symfony\Component\Form\FormBuilder
    ;

class MenuItemType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('label')
            ->add('uri')
            ->add('yml_attributes', 'textarea')
            ->add('yml_link_attributes', 'textarea')
            ->add('yml_label_attributes', 'textarea')
            ;
    }

    public function getName()
    {
        return 'menu_item';
    }
}

