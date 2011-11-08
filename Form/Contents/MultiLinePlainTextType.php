<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Contents;

use
    Symfony\Component\Form\AbstractType
    ,Symfony\Component\Form\FormBuilder
    ;

class MultiLinePlainTextType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('content', 'textarea');
    }

    public function getName()
    {
        return 'multi_line_plain_text';
    }
}

