<?php

namespace MuchoMasFacil\InPageEditBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('show_legend', false); // no legend for main form
        $builder
            ->add('ipe_handler')
            ->add('header')
            ->add('content')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MuchoMasFacil\InPageEditBundle\Entity\Content'
        ));
    }

    public function getName()
    {
        return 'muchomasfacil_inpageeditbundle_contenttype';
    }
}
