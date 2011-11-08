<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Contents;

use
    Symfony\Component\Form\AbstractType
    ,Symfony\Component\Form\FormBuilder
    ;

class PlainTextType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('content', 'text');
    }

    public function getName()
    {
        return 'plain_text';
    }
}

