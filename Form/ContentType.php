<?php

namespace MuchoMasFacil\InPageEditBundle\Form;

use
    Symfony\Component\Form\AbstractType
    ,Symfony\Component\Form\FormBuilder
    ;

class ContentType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('yml_params', 'textarea')
            ->add('yml_editor_roles', 'textarea')
            ->add('yml_admin_roles', 'textarea')
            ->add('entity_class', 'text')
            ->add('form_class', 'text')
            ->add('collection_length', 'integer')
            ->add('render_template', 'text');
    }

    public function getName()
    {
        return 'content';
    }
}

